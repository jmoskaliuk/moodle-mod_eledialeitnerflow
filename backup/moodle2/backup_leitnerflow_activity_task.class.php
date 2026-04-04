<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/leitnerflow/backup/moodle2/backup_leitnerflow_stepslib.php');

class backup_leitnerflow_activity_task extends backup_activity_task {

    protected function define_my_settings(): void {}

    protected function define_my_steps(): void {
        $this->add_step(new backup_leitnerflow_activity_structure_step(
            'leitnerflow_structure', 'leitnerflow.xml'
        ));
    }

    public static function encode_content_links($content): string {
        global $CFG;
        $base = preg_quote($CFG->wwwroot, '/');

        // view.php links
        $search  = "/({$base}\/mod\/leitnerflow\/view\.php\?id=)([0-9]+)/";
        $content = preg_replace($search, '$@LEITNERFLOWVIEWBYID*$2@$', $content);

        return $content;
    }
}
