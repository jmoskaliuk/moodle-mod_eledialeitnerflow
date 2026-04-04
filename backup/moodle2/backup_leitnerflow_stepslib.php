<?php
defined('MOODLE_INTERNAL') || die();

class backup_leitnerflow_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure(): backup_nested_element {
        $userinfo = $this->get_setting_value('userinfo');

        // Root: leitnerflow instance
        $leitnerflow = new backup_nested_element('leitnerflow', ['id'], [
            'name', 'intro', 'introformat',
            'questioncategoryid', 'sessionsize', 'boxcount',
            'correcttolearn', 'wrongbehavior', 'questionrotation',
            'prioritystrategy', 'grade', 'grademethod',
            'timecreated', 'timemodified',
        ]);

        // Card states per user
        $cardstates = new backup_nested_element('card_states');
        $cardstate  = new backup_nested_element('card_state', ['id'], [
            'userid', 'questionid', 'currentbox', 'correctcount',
            'attemptcount', 'status', 'timecreated', 'timemodified',
        ]);

        // Sessions per user
        $sessions = new backup_nested_element('sessions');
        $session  = new backup_nested_element('session', ['id'], [
            'userid', 'questionids', 'currentindex',
            'questionsasked', 'questionscorrect', 'status',
            'timecreated', 'timecompleted',
        ]);

        // Build tree
        $leitnerflow->add_child($cardstates);
        $cardstates->add_child($cardstate);
        $leitnerflow->add_child($sessions);
        $sessions->add_child($session);

        // Data sources
        $leitnerflow->set_source_table('leitnerflow', ['id' => backup::VAR_ACTIVITYID]);

        if ($userinfo) {
            $cardstate->set_source_table('leitnerflow_card_state',
                ['leitnerflowid' => backup::VAR_PARENTID]);
            $cardstate->annotate_ids('user', 'userid');
            $cardstate->annotate_ids('question', 'questionid');

            $session->set_source_table('leitnerflow_sessions',
                ['leitnerflowid' => backup::VAR_PARENTID]);
            $session->annotate_ids('user', 'userid');
        }

        $leitnerflow->annotate_files('mod_leitnerflow', 'intro', null);

        return $this->prepare_activity_structure($leitnerflow);
    }
}
