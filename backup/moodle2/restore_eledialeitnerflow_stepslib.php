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
 * Restore steps for mod_eledialeitnerflow.
 *
 * @package    mod_eledialeitnerflow
 * @copyright  2026 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Activity restoration structure step class.
 *
 * @package    mod_eledialeitnerflow
 * @copyright  2026 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_eledialeitnerflow_activity_structure_step extends restore_questions_activity_structure_step {
    /**
     * @var int Last restored session for the QE usage callback.
     */
    private $currentsessionid;

    /**
     * Define the structure for restoration.
     *
     * @return array Array of restore path elements.
     */
    protected function define_structure(): array {
        $paths   = [];
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('eledialeitnerflow', '/activity/eledialeitnerflow');

        if ($userinfo) {
            $paths[] = new restore_path_element(
                'eledialeitnerflow_card_state',
                '/activity/eledialeitnerflow/card_states/card_state'
            );
            $sessionpath = new restore_path_element(
                'eledialeitnerflow_session',
                '/activity/eledialeitnerflow/sessions/session'
            );
            $paths[] = $sessionpath;
            $this->add_question_usages($sessionpath, $paths);
            $paths[] = new restore_path_element(
                'eledialeitnerflow_external_attempt',
                '/activity/eledialeitnerflow/external_attempts/external_attempt'
            );
            $paths[] = new restore_path_element(
                'eledialeitnerflow_external_result',
                '/activity/eledialeitnerflow/external_attempts/external_attempt/external_results/external_result'
            );
        }

        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process eledialeitnerflow activity data during restoration.
     *
     * @param array $data The activity data.
     * @return void
     */
    protected function process_eledialeitnerflow(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->course = $this->get_courseid();
        $data->externalenabled = 0; // Re-authorise sources explicitly after restore.
        $data->timecreated  = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $categoryids = $this->remap_question_category_ids($data->questioncategoryids ?? '');
        if (!empty($categoryids)) {
            $data->questioncategoryids = implode(',', $categoryids);
            $data->questioncategoryid = reset($categoryids);
        } else {
            $mappedcategoryid = $this->remap_question_category_id((int)($data->questioncategoryid ?? 0));
            $data->questioncategoryid = $mappedcategoryid;
            $data->questioncategoryids = $mappedcategoryid > 0 ? (string)$mappedcategoryid : null;
        }

        $newid = $DB->insert_record('eledialeitnerflow', $data);
        $this->apply_activity_instance($newid);
    }

    /**
     * Process card state data during restoration.
     *
     * @param array $data The card state data.
     * @return void
     */
    protected function process_eledialeitnerflow_card_state(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->eledialeitnerflowid = $this->get_new_parentid('eledialeitnerflow');
        $data->userid        = $this->get_mappingid('user', $data->userid);
        $data->questionid    = $this->remap_question_id((int)$data->questionid);
        $data->timecreated   = $this->apply_date_offset($data->timecreated);
        $data->timemodified  = $this->apply_date_offset($data->timemodified);

        if ($data->userid && $data->questionid) {
            $DB->insert_record('eledialeitnerflow_card_state', $data);
        }
    }

    /**
     * Process session data during restoration.
     *
     * @param array $data The session data.
     * @return void
     */
    protected function process_eledialeitnerflow_session(array $data): void {
        global $DB;

        $data = (object)$data;
        $data->eledialeitnerflowid  = $this->get_new_parentid('eledialeitnerflow');
        $data->userid         = $this->get_mappingid('user', $data->userid);
        $data->timecreated    = $this->apply_date_offset($data->timecreated);
        $data->timecompleted  = !empty($data->timecompleted)
            ? $this->apply_date_offset($data->timecompleted) : null;
        if (!empty($data->sceneid) && $data->sceneid !== 'none') {
            throw new moodle_exception('legacymigrationrequired', 'mod_eledialeitnerflow');
        }
        $oldid = $data->id;
        $data->qubaid = null;
        $ids = array_map(fn($qid) => $this->remap_question_id((int)$qid), json_decode($data->questionids ?: '[]', true));
        $data->questionids = json_encode($ids);
        // Old backups did not contain QE data; they remain completed historical summaries.
        $data->status = 1;
        if ($data->userid) {
            $this->currentsessionid = $DB->insert_record('eledialeitnerflow_sessions', $data);
            $this->set_mapping('eledialeitnerflow_session', $oldid, $this->currentsessionid);
        }
    }

    /**
     * Attach the restored Question Engine usage to its owning session.
     * @param int $newusageid Usage ID.
     */
    protected function inform_new_usage_id($newusageid) {
        global $DB;
        if ($this->currentsessionid) {
            $DB->set_field('eledialeitnerflow_sessions', 'qubaid', $newusageid, ['id' => $this->currentsessionid]);
        }
    }

    /**
     * Restore source provenance closed, without ever reapplying a card result.
     *
     * @param array $data Backup data.
     */
    protected function process_eledialeitnerflow_external_attempt(array $data): void {
        global $DB;
        $data = (object)$data;
        $oldid = $data->id;
        unset($data->id);
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->eledialeitnerflowid = $this->get_new_parentid('eledialeitnerflow');
        $data->sourcecmid = 0;
        $data->qubaid = 0;
        $data->closed = 1;
        $data->attemptuuid = bin2hex(random_bytes(16));
        $data->questions = '{}';
        if ($data->userid) {
            $newid = $DB->insert_record('eledialeitnerflow_ext_attempts', $data);
            $this->set_mapping('eledialeitnerflow_external_attempt', $oldid, $newid);
        }
    }

    /**
     * Restore historical receipts as evidence, never as new review submissions.
     *
     * @param array $data Backup data.
     */
    protected function process_eledialeitnerflow_external_result(array $data): void {
        global $DB;
        $data = (object)$data;
        unset($data->id);
        $data->registrationid = $this->get_new_parentid('eledialeitnerflow_external_attempt');
        $data->questionid = $this->remap_question_id((int)$data->questionid);
        if ($data->registrationid && $data->questionid) {
            $DB->insert_record('eledialeitnerflow_ext_results', $data);
        }
    }

    /**
     * Follow core QE restore semantics: same-site shared questions may have no mapping.
     * @param int $id Original question ID.
     * @return int Restored or reused question ID.
     */
    private function remap_question_id(int $id): int {
        global $DB;
        $mapped = $this->get_mappingid('question', $id);
        if (!$mapped && $this->task->is_samesite() && $DB->record_exists('question', ['id' => $id])) {
            return $id;
        }
        return (int)$mapped;
    }

    /**
     * Remap a comma-separated list of question category IDs.
     *
     * @param string|null $categoryids Original category IDs.
     * @return array New category IDs.
     */
    private function remap_question_category_ids(?string $categoryids): array {
        $ids = array_filter(array_map('intval', explode(',', (string)$categoryids)));
        $mappedids = [];
        foreach ($ids as $id) {
            $mappedid = $this->remap_question_category_id($id);
            if ($mappedid > 0) {
                $mappedids[] = $mappedid;
            }
        }
        return array_values(array_unique($mappedids));
    }

    /**
     * Remap one question category ID, preserving same-site IDs only as fallback.
     *
     * @param int $categoryid Original category ID.
     * @return int New category ID, or 0 if unavailable.
     */
    private function remap_question_category_id(int $categoryid): int {
        global $DB;

        if ($categoryid <= 0) {
            return 0;
        }

        $mappedid = $this->get_mappingid('question_category', $categoryid);
        if ($mappedid) {
            return (int)$mappedid;
        }

        if ($this->get_task()->is_samesite() && $DB->record_exists('question_categories', ['id' => $categoryid])) {
            return $categoryid;
        }

        return 0;
    }

    /**
     * Perform actions after restoration is complete.
     *
     * @return void
     */
    protected function after_execute(): void {
        global $DB;
        parent::after_execute();
        $this->add_related_files('mod_eledialeitnerflow', 'intro', null);
    }
}
