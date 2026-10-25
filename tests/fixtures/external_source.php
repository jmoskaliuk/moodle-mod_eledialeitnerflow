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


/**
 * Test-only independent module callback; no Fahrup code is loaded.
 *
 * @param stdClass $registration Registration.
 */
function eledialeitnerflow_validate_leitnerflow_review(stdClass $registration): bool {
    global $DB, $USER;
    return $DB->record_exists('eledialeitnerflow_sessions', ['eledialeitnerflowid' =>
        get_coursemodule_from_id('eledialeitnerflow', $registration->sourcecmid)->instance,
        'userid' => $USER->id, 'qubaid' => $registration->qubaid]);
}
