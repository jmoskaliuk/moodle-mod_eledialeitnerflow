<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/leitnerflow/backup/moodle2/restore_leitnerflow_stepslib.php');

class restore_leitnerflow_activity_task extends restore_activity_task {

    protected function define_my_settings(): void {}

    protected function define_my_steps(): void {
        $this->add_step(new restore_leitnerflow_activity_structure_step(
            'leitnerflow_structure', 'leitnerflow.xml'
        ));
    }

    public static function define_decode_contents(): array {
        $contents = [];
        $contents[] = new restore_decode_content('leitnerflow', ['intro'], 'leitnerflow');
        return $contents;
    }

    public static function define_decode_rules(): array {
        $rules = [];
        $rules[] = new restore_decode_rule(
            'LEITNERFLOWVIEWBYID', '/mod/leitnerflow/view.php?id=$1', 'course_module'
        );
        return $rules;
    }

    public static function define_restore_log_rules(): array {
        return [];
    }

    public static function define_restore_log_rules_for_course(): array {
        return [];
    }
}
