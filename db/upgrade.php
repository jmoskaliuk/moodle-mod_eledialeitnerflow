<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_leitnerflow_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    // Future upgrade steps go here.
    return true;
}
