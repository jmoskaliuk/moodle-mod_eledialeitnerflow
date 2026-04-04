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
 * Backup task for mod_leitnerflow.
 *
 * @package    mod_leitnerflow
 * @copyright  2024 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
