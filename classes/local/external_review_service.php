<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version file for mod_eledialeitnerflow.
 *
 * @package    mod_eledialeitnerflow
 * @copyright  2026 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_eledialeitnerflow\local;

/**
 * Generic in-process review contract. No source plugin is a required dependency.
 */
class external_review_service {
    /**
     * @var array Reentrant locks held by this request, never released before outer commit.
     */
    private static $held = [];

    /**
     * Version of the public PHP contract.
     */
    public const API_VERSION = 1;

    /**
     * Serialize native and external card mutations for one activity and user.
     *
     * @param int $instanceid Instanceid.
     * @param int $userid Userid.
     * @param callable $work Work.
     */
    public static function locked(int $instanceid, int $userid, callable $work) {
        global $DB;
        $key = $instanceid . ':' . $userid;
        if (isset(self::$held[$key])) {
            return $work();
        }
        $lock = \core\lock\lock_config::get_lock_factory('mod_eledialeitnerflow')->get_lock($instanceid . ':' . $userid, 10);
        if (!$lock) {
            throw new \moodle_exception('reviewbusy', 'mod_eledialeitnerflow');
        }
        self::$held[$key] = true;
        try {
            $transaction = $DB->start_delegated_transaction();
            try {
                $result = $work();
                $transaction->allow_commit();
                return $result;
            } catch (\Throwable $e) {
                $transaction->rollback($e);
            }
        } finally {
            unset(self::$held[$key]);
            $lock->release();
        }
    }

    /**
     * Read a target configuration with explicit same-course and module access checks.
     *
     * @param int $cmid Cmid.
     * @param int $courseid Courseid.
     */
    public static function target_configuration(int $cmid, int $courseid): \stdClass {
        global $DB;
        $cm = get_coursemodule_from_id('eledialeitnerflow', $cmid, 0, false, MUST_EXIST);
        if ((int)$cm->course !== $courseid) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
        require_capability('mod/eledialeitnerflow:view', \context_module::instance($cmid));
        return $DB->get_record('eledialeitnerflow', ['id' => $cm->instance], '*', MUST_EXIST);
    }

    /**
     * Check whether this user's registration can still accept results.
     *
     * @param int $id Registration ID.
     * @return bool
     */
    public static function is_registration_open(int $id): bool {
        global $DB, $USER;
        $record = $DB->get_record('eledialeitnerflow_ext_attempts', ['id' => $id, 'userid' => $USER->id]);
        return $record && !$record->closed;
    }

    /**
     * Close a registration only from its owning source or an administrative operation.
     *
     * @param int $id Id.
     */
    public static function close_attempt(int $id): void {
        global $DB;
        $row = $DB->get_record('eledialeitnerflow_ext_attempts', ['id' => $id]);
        if ($row) {
            $DB->set_field('eledialeitnerflow_ext_attempts', 'closed', 1, ['id' => $id]);
        }
    }

    /**
     * Validate the target and trusted source contract, including attempt ownership.
     *
     * @param \stdClass $registration Registration.
     */
    public static function authorise(\stdClass $registration): \stdClass {
        global $DB, $CFG, $USER;
        require_once($CFG->dirroot . '/question/engine/lib.php');
        $cm = get_coursemodule_from_instance('eledialeitnerflow', $registration->eledialeitnerflowid, 0, false, MUST_EXIST);
        $context = \context_module::instance($cm->id);
        require_capability('mod/eledialeitnerflow:attempt', $context);
        $instance = $DB->get_record('eledialeitnerflow', ['id' => $cm->instance], '*', MUST_EXIST);
        $source = get_coursemodule_from_id('', $registration->sourcecmid, 0, false, MUST_EXIST);
        $allowed = preg_split('/[\s,]+/', (string)get_config('mod_eledialeitnerflow', 'reviewsources'), -1, PREG_SPLIT_NO_EMPTY);
        $component = 'mod_' . $source->modname;
        if (
            !$instance->externalenabled || !in_array($component, $allowed, true) ||
                $registration->sourcecomponent !== $component || (int)$registration->userid !== (int)$USER->id ||
                (int)$source->course !== (int)$cm->course
        ) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
        $modinfo = get_fast_modinfo($cm->course, $USER->id);
        if (!$modinfo->get_cm($cm->id)->uservisible || !$modinfo->get_cm($source->id)->uservisible) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
        $lib = \core_component::get_component_directory($component) . '/lib.php';
        if (!is_readable($lib)) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
        require_once($lib);
        $callback = $source->modname . '_validate_leitnerflow_review';
        if (!is_callable($callback) || $callback(clone $registration) !== true) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
        $usage = \question_engine::load_questions_usage_by_activity($registration->qubaid);
        if (
            $usage->get_owning_component() !== $component ||
                (int)$usage->get_owning_context()->id !== (int)\context_module::instance($source->id)->id
        ) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
        return $instance;
    }

    /**
     * Register a new source-owned review before any answer is graded.
     *
     * @param int $targetcmid Targetcmid.
     * @param int $sourcecmid Sourcecmid.
     * @param string $uuid Uuid.
     * @param int $qubaid Qubaid.
     */
    public static function register_attempt(int $targetcmid, int $sourcecmid, string $uuid, int $qubaid): int {
        global $DB, $USER, $CFG;
        require_once($CFG->dirroot . '/question/engine/lib.php');
        if (!preg_match('/^[a-zA-Z0-9-]{16,64}$/D', $uuid)) {
            throw new \invalid_parameter_exception('Invalid attempt identity');
        }
        $target = get_coursemodule_from_id('eledialeitnerflow', $targetcmid, 0, false, MUST_EXIST);
        $source = get_coursemodule_from_id('', $sourcecmid, 0, false, MUST_EXIST);
        $record = (object)['eledialeitnerflowid' => $target->instance, 'sourcecmid' => $sourcecmid,
            'sourcecomponent' => 'mod_' . $source->modname, 'attemptuuid' => $uuid, 'qubaid' => $qubaid,
            'userid' => $USER->id, 'closed' => 0, 'timecreated' => time()];
        $instance = self::authorise($record);
        return self::locked($target->instance, $USER->id, function () use ($DB, $record, $instance) {
            $old = $DB->get_record(
                'eledialeitnerflow_ext_attempts',
                ['sourcecmid' => $record->sourcecmid, 'attemptuuid' => $record->attemptuuid]
            );
            if ($old) {
                foreach (['userid', 'qubaid', 'eledialeitnerflowid', 'sourcecomponent'] as $field) {
                    if ((string)$old->$field !== (string)$record->$field) {
                        throw new \invalid_parameter_exception('Attempt identity reused');
                    }
                }
                if ($old->closed) {
                    throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
                }
                return (int)$old->id;
            }
            $quba = \question_engine::load_questions_usage_by_activity($record->qubaid);
            $questions = [];
            foreach ($quba->get_slots() as $slot) {
                if ($quba->get_question_state($slot)->is_finished()) {
                    throw new \invalid_parameter_exception('Review must be registered before grading');
                }
                self::validate_question($instance, (int)$quba->get_question($slot)->id);
                $questions[$slot] = $quba->get_question($slot)->id;
            }
            $record->questions = json_encode($questions);
            return (int)$DB->insert_record('eledialeitnerflow_ext_attempts', $record);
        });
    }

    /**
     * Apply a final QE slot at most once. All correctness comes from QE.
     *
     * @param int $registrationid Registrationid.
     * @param int $slot Slot.
     */
    public static function commit_slot(int $registrationid, int $slot): \stdClass {
        global $DB, $USER;
        $registration = $DB->get_record('eledialeitnerflow_ext_attempts', ['id' => $registrationid], '*', MUST_EXIST);
        self::authorise($registration);
        return self::locked($registration->eledialeitnerflowid, $USER->id, function () use ($DB, $registrationid, $slot) {
            $registration = $DB->get_record('eledialeitnerflow_ext_attempts', ['id' => $registrationid], '*', MUST_EXIST);
            $instance = self::authorise($registration);
            if ($registration->closed) {
                throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
            }
            $old = $DB->get_record('eledialeitnerflow_ext_results', ['registrationid' => $registrationid, 'slot' => $slot]);
            if ($old) {
                return $old;
            }
            $quba = \question_engine::load_questions_usage_by_activity($registration->qubaid);
            $questions = json_decode($registration->questions, true);
            if (
                !isset($questions[$slot]) || (int)$questions[$slot] !== (int)$quba->get_question($slot)->id ||
                    !$quba->get_question_state($slot)->is_finished() || $quba->get_question_fraction($slot) === null
            ) {
                throw new \invalid_parameter_exception('Not a final registered slot');
            }
            $qid = (int)$questions[$slot];
            self::validate_question($instance, $qid);
            $engine = \mod_eledialeitnerflow\engine\leitner_engine::class;
            $state = $engine::process_answer(
                $engine::get_card_state($instance->id, $registration->userid, $qid),
                $quba->get_question_fraction($slot) >= 1.0,
                $instance,
                $qid,
                $registration->userid
            );
            $engine::save_card_state($state);
            $result = (object)['registrationid' => $registrationid, 'slot' => $slot,
                'questionid' => $qid, 'timeapplied' => time()];
            $result->id = $DB->insert_record('eledialeitnerflow_ext_results', $result);
            global $CFG;
            require_once($CFG->dirroot . '/mod/eledialeitnerflow/lib.php');
            eledialeitnerflow_update_grades($instance, $registration->userid);
            return $result;
        });
    }

    /**
     * Check membership of the concrete question version, preserving pinned versions.
     *
     * @param \stdClass $instance Instance.
     * @param int $questionid Questionid.
     */
    public static function validate_question(\stdClass $instance, int $questionid): void {
        global $DB;
        $category = $DB->get_field_sql('SELECT e.questioncategoryid FROM {question_versions} v
            JOIN {question_bank_entries} e ON e.id = v.questionbankentryid
            WHERE v.questionid = :qid AND v.status = :status', ['qid' => $questionid, 'status' => 'ready']);
        $categories = \mod_eledialeitnerflow\engine\leitner_engine::get_category_ids($instance);
        if (!$category || !in_array((int)$category, array_map('intval', $categories), true)) {
            throw new \moodle_exception('invalidreviewsource', 'mod_eledialeitnerflow');
        }
    }

    /**
     * Revoke a source's existing registrations without changing card knowledge.
     *
     * @param int $sourcecmid Sourcecmid.
     * @param int|null $userid Userid.
     */
    public static function revoke_source(int $sourcecmid, ?int $userid = null): void {
        global $DB;
        $where = ['sourcecmid' => $sourcecmid];
        if ($userid !== null) {
            $where['userid'] = $userid;
        }
        $DB->set_field('eledialeitnerflow_ext_attempts', 'closed', 1, $where);
    }

    /**
     * Remove personal provenance during approved privacy or activity deletion.
     *
     * @param int $instanceid Instanceid.
     * @param int|null $userid Userid.
     */
    public static function delete_provenance(int $instanceid, ?int $userid = null): void {
        global $DB;
        $where = ['eledialeitnerflowid' => $instanceid];
        if ($userid !== null) {
            $where['userid'] = $userid;
        }
        foreach ($DB->get_records('eledialeitnerflow_ext_attempts', $where) as $record) {
            $DB->delete_records('eledialeitnerflow_ext_results', ['registrationid' => $record->id]);
            $DB->delete_records('eledialeitnerflow_ext_attempts', ['id' => $record->id]);
        }
    }
}
