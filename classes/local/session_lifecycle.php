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
 * Own the lifecycle of ordinary LeitnerFlow sessions.
 */
class session_lifecycle {
    /**
     * Delete a session and its own Question Engine usage.
     *
     * @param \stdClass $session Session.
     */
    public static function delete(\stdClass $session): void {
        global $DB, $CFG;
        require_once($CFG->dirroot . '/question/engine/lib.php');
        if ($session->qubaid) {
            \question_engine::delete_questions_usage_by_activity($session->qubaid);
        }
        $DB->delete_records('eledialeitnerflow_sessions', ['id' => $session->id]);
    }
}
