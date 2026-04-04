<?php
namespace mod_leitnerflow\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\deletion_criteria;
use core_privacy\local\request\helper;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;

class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider,
    \core_privacy\local\request\core_userlist_provider {

    public static function get_metadata(collection $collection): collection {

        $collection->add_database_table(
            'leitnerflow_card_state',
            [
                'userid'        => 'privacy:metadata:leitnerflow_card_state:userid',
                'questionid'    => 'privacy:metadata:leitnerflow_card_state:questionid',
                'currentbox'    => 'privacy:metadata:leitnerflow_card_state:currentbox',
                'correctcount'  => 'privacy:metadata:leitnerflow_card_state:correctcount',
                'attemptcount'  => 'privacy:metadata:leitnerflow_card_state:attemptcount',
                'status'        => 'privacy:metadata:leitnerflow_card_state:status',
            ],
            'privacy:metadata:leitnerflow_card_state'
        );

        $collection->add_database_table(
            'leitnerflow_sessions',
            [
                'userid'            => 'privacy:metadata:leitnerflow_sessions:userid',
                'timecreated'       => 'privacy:metadata:leitnerflow_sessions:timecreated',
                'timecompleted'     => 'privacy:metadata:leitnerflow_sessions:timecompleted',
                'questionsasked'    => 'privacy:metadata:leitnerflow_sessions:questionsasked',
                'questionscorrect'  => 'privacy:metadata:leitnerflow_sessions:questionscorrect',
            ],
            'privacy:metadata:leitnerflow_sessions'
        );

        return $collection;
    }

    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :ctxmodule
                  JOIN {leitnerflow} lq    ON lq.id = cm.instance
                  JOIN {leitnerflow_card_state} cs ON cs.leitnerflowid = lq.id AND cs.userid = :userid";
        $contextlist->add_from_sql($sql, ['ctxmodule' => CONTEXT_MODULE, 'userid' => $userid]);
        return $contextlist;
    }

    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        $sql = "SELECT cs.userid
                  FROM {leitnerflow_card_state} cs
                  JOIN {course_modules} cm ON cm.instance = cs.leitnerflowid
                 WHERE cm.id = :cmid";
        $userlist->add_from_sql('userid', $sql, ['cmid' => $context->instanceid]);
    }

    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id('leitnerflow', $context->instanceid);
            if (!$cm) {
                continue;
            }
            $userid = $contextlist->get_user()->id;

            // Export card states
            $states = $DB->get_records('leitnerflow_card_state', [
                'leitnerflowid' => $cm->instance,
                'userid'        => $userid,
            ]);
            if ($states) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:metadata:leitnerflow_card_state', 'mod_leitnerflow')],
                    (object)['card_states' => array_values($states)]
                );
            }

            // Export sessions
            $sessions = $DB->get_records('leitnerflow_sessions', [
                'leitnerflowid' => $cm->instance,
                'userid'        => $userid,
            ]);
            if ($sessions) {
                writer::with_context($context)->export_data(
                    [get_string('privacy:metadata:leitnerflow_sessions', 'mod_leitnerflow')],
                    (object)['sessions' => array_values($sessions)]
                );
            }
        }
    }

    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        $cm = get_coursemodule_from_id('leitnerflow', $context->instanceid);
        if (!$cm) {
            return;
        }

        $sessions = $DB->get_records('leitnerflow_sessions', ['leitnerflowid' => $cm->instance]);
        foreach ($sessions as $session) {
            if (!empty($session->qubaid)) {
                \question_engine::delete_questions_usage_by_activity($session->qubaid);
            }
        }
        $DB->delete_records('leitnerflow_sessions',   ['leitnerflowid' => $cm->instance]);
        $DB->delete_records('leitnerflow_card_state', ['leitnerflowid' => $cm->instance]);
    }

    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;
        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id('leitnerflow', $context->instanceid);
            if (!$cm) {
                continue;
            }
            \mod_leitnerflow\engine\leitner_engine::delete_user_data($cm->instance, $userid);
        }
    }

    public static function delete_data_for_users(approved_userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        $cm = get_coursemodule_from_id('leitnerflow', $context->instanceid);
        if (!$cm) {
            return;
        }
        foreach ($userlist->get_userids() as $userid) {
            \mod_leitnerflow\engine\leitner_engine::delete_user_data($cm->instance, $userid);
        }
    }
}
