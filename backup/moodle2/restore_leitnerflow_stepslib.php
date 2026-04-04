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
 * Restore steps for mod_leitnerflow.
 *
 * @package    mod_leitnerflow
 * @copyright  2024 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class restore_leitnerflow_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure(): array {
        $paths   = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('leitnerflow', '/activity/leitnerflow');

        if ($userinfo) {
            $paths[] = new restore_path_element(
                'leitnerflow_card_state',
                '/activity/leitnerflow/card_states/card_state'
            );
            $paths[] = new restore_path_element(
                'leitnerflow_session',
                '/activity/leitnerflow/sessions/session'
            );
        }

        return $this->prepare_activity_structure($paths);
    }

    protected function process_leitnerflow(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        // Note: questioncategoryid mapping is handled by annotate_ids in backup.
        // For simplicity in restore we keep the original (same-site restore).
        // Cross-site restores would need a category mapping step.

        $newid = $DB->insert_record('leitnerflow', $data);
        $this->apply_activity_instance($newid);
    }

    protected function process_leitnerflow_card_state(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->leitnerflowid = $this->get_new_parentid('leitnerflow');
        $data->userid        = $this->get_mappingid('user', $data->userid);
        $data->questionid    = $this->get_mappingid('question', $data->questionid);
        $data->timecreated   = $this->apply_date_offset($data->timecreated);
        $data->timemodified  = $this->apply_date_offset($data->timemodified);

        if ($data->userid && $data->questionid) {
            $DB->insert_record('leitnerflow_card_state', $data);
        }
    }

    protected function process_leitnerflow_session(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->leitnerflowid  = $this->get_new_parentid('leitnerflow');
        $data->userid         = $this->get_mappingid('user', $data->userid);
        $data->timecreated    = $this->apply_date_offset($data->timecreated);
        $data->timecompleted  = !empty($data->timecompleted)
            ? $this->apply_date_offset($data->timecompleted) : null;
        // qubaid is not restored (question_usages are not portable)
        $data->qubaid = null;
        $data->status = 1; // mark as completed so no stale active sessions

        if ($data->userid) {
            $DB->insert_record('leitnerflow_sessions', $data);
        }
    }

    protected function after_execute(): void {
        $this->add_related_files('mod_leitnerflow', 'intro', null);
    }
}
