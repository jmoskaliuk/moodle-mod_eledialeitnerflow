<?php
// This file is part of Moodle - http://moodle.org/
//
// mod_leitnerflow test data generator.
// Lets test classes create fully configured leitnerflow instances
// and card states with a single method call.

defined('MOODLE_INTERNAL') || die();

class mod_leitnerflow_generator extends testing_module_generator {

    /**
     * Create a leitnerflow activity with sensible Leitner defaults.
     *
     * All settings can be overridden via $record:
     *   correcttolearn, boxcount, wrongbehavior, sessionsize, etc.
     *
     * @param array|stdClass $record
     * @param array          $options
     * @return stdClass  The course-module record (has ->instance for leitnerflow id)
     */
    public function create_instance($record = null, array $options = null): stdClass {
        $record = (object)(array)($record ?? []);

        // Default Leitner settings
        $defaults = [
            'name'               => 'Test Leitner Flow ' . $this->instancecount,
            'intro'              => 'Test intro',
            'introformat'        => FORMAT_HTML,
            'questioncategoryid' => 0,   // Will be set by tests that need real questions
            'sessionsize'        => 10,
            'boxcount'           => 3,
            'correcttolearn'     => 3,
            'wrongbehavior'      => 0,   // WRONG_RESET
            'questionrotation'   => 1,
            'prioritystrategy'   => 0,
            'grade'              => 0,
            'grademethod'        => 0,
        ];

        foreach ($defaults as $key => $val) {
            if (!isset($record->$key)) {
                $record->$key = $val;
            }
        }

        return parent::create_instance($record, $options);
    }

    /**
     * Create a card state record directly in the DB.
     * Useful for tests that need to set up a specific Leitner state.
     *
     * @param array $data  Keys: leitnerflowid, userid, questionid, currentbox,
     *                     correctcount, attemptcount, status
     * @return stdClass  The inserted record
     */
    public function create_card_state(array $data): stdClass {
        global $DB;

        $defaults = [
            'currentbox'   => 1,
            'correctcount' => 0,
            'attemptcount' => 0,
            'status'       => 0,   // STATUS_OPEN
            'timecreated'  => time(),
            'timemodified' => time(),
        ];

        $record = (object)array_merge($defaults, $data);

        // Upsert: delete existing state for same quiz+user+question if present
        $DB->delete_records('leitnerflow_card_state', [
            'leitnerflowid' => $record->leitnerflowid,
            'userid'        => $record->userid,
            'questionid'    => $record->questionid,
        ]);

        $record->id = $DB->insert_record('leitnerflow_card_state', $record);
        return $record;
    }

    /**
     * Create a completed session record.
     *
     * @param array $data  Keys: leitnerflowid, userid, questionsasked, questionscorrect
     * @return stdClass
     */
    public function create_session(array $data): stdClass {
        global $DB;

        $defaults = [
            'qubaid'           => null,
            'questionids'      => json_encode([]),
            'currentindex'     => 0,
            'questionsasked'   => 5,
            'questionscorrect' => 3,
            'status'           => 1,  // completed
            'timecreated'      => time() - 3600,
            'timecompleted'    => time(),
        ];

        $record    = (object)array_merge($defaults, $data);
        $record->id = $DB->insert_record('leitnerflow_sessions', $record);
        return $record;
    }
}
