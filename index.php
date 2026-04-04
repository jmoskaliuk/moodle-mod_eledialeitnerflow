<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$courseid = required_param('id', PARAM_INT);
$course   = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);

require_login($course);
$PAGE->set_url('/mod/leitnerflow/index.php', ['id' => $courseid]);
$PAGE->set_title(format_string($course->shortname) . ': ' . get_string('modulenameplural', 'mod_leitnerflow'));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'mod_leitnerflow'));

$instances = get_all_instances_in_course('leitnerflow', $course);
if (empty($instances)) {
    notice(get_string('thereareno', 'moodle', get_string('modulenameplural', 'mod_leitnerflow')),
        new moodle_url('/course/view.php', ['id' => $courseid]));
}

$table          = new html_table();
$table->head    = [get_string('name')];
$table->colclasses = ['leftalign'];

foreach ($instances as $lq) {
    $link = html_writer::link(
        new moodle_url('/mod/leitnerflow/view.php', ['id' => $lq->coursemodule]),
        format_string($lq->name)
    );
    $table->data[] = [$link];
}
echo html_writer::table($table);
echo $OUTPUT->footer();
