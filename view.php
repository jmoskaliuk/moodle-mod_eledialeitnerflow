<?php
// Main view page: shows progress overview and lets students start/continue sessions.
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_leitnerflow\engine\leitner_engine;

$id = required_param('id', PARAM_INT); // course module id

$cm         = get_coursemodule_from_id('leitnerflow', $id, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$leitnerflow = $DB->get_record('leitnerflow', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = \core\context\module::instance($cm->id);

// Completion tracking
$completion = new completion_info($course);
$completion->set_module_viewed($cm);

$PAGE->set_url('/mod/leitnerflow/view.php', ['id' => $cm->id]);
$PAGE->set_title(format_string($leitnerflow->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('mod-leitnerflow');

// ---- Handle reset action (teacher only) ------------------------------------
if (optional_param('resetuserid', 0, PARAM_INT) > 0) {
    require_capability('mod/leitnerflow:resetprogress', $context);
    require_sesskey();
    $resetuid = required_param('resetuserid', PARAM_INT);
    leitner_engine::delete_user_data($leitnerflow->id, $resetuid);
    redirect(new moodle_url('/mod/leitnerflow/view.php', ['id' => $cm->id]),
        get_string('progressreset', 'mod_leitnerflow'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// ---- Determine role --------------------------------------------------------
$isteacher = has_capability('mod/leitnerflow:viewreport', $context);
$canAttempt = has_capability('mod/leitnerflow:attempt', $context);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($leitnerflow->name));

// Show intro
if ($leitnerflow->intro) {
    echo $OUTPUT->box(format_module_intro('leitnerflow', $leitnerflow, $cm->id), 'generalbox');
}

// ---- Student view ----------------------------------------------------------
if ($canAttempt) {
    $stats = leitner_engine::get_user_stats($leitnerflow->id, $USER->id, $leitnerflow->questioncategoryid);

    // Check for active session
    $activesession = $DB->get_record('leitnerflow_sessions', [
        'leitnerflowid' => $leitnerflow->id,
        'userid'        => $USER->id,
        'status'        => 0,
    ]);

    // Render progress card
    $percent = $stats->total > 0 ? ($stats->learned / $stats->total * 100) : 0;

    echo html_writer::start_div('leitnerflow-progress-card card mb-4');
    echo html_writer::start_div('card-body');
    echo html_writer::tag('h5', get_string('yourprogress', 'mod_leitnerflow'), ['class' => 'card-title']);

    // Stats row
    echo html_writer::start_div('row text-center mb-3');
    $statitems = [
        ['label' => get_string('totalcards', 'mod_leitnerflow'), 'value' => $stats->total,   'class' => ''],
        ['label' => get_string('learned',    'mod_leitnerflow'), 'value' => $stats->learned,  'class' => 'text-success'],
        ['label' => get_string('open',       'mod_leitnerflow'), 'value' => $stats->open,     'class' => 'text-warning'],
        ['label' => get_string('witherrors', 'mod_leitnerflow'), 'value' => $stats->errors,   'class' => 'text-danger'],
    ];
    foreach ($statitems as $item) {
        echo html_writer::start_div('col-3');
        echo html_writer::tag('div', $item['value'], ['class' => 'h3 mb-0 ' . $item['class']]);
        echo html_writer::tag('small', $item['label'], ['class' => 'text-muted']);
        echo html_writer::end_div();
    }
    echo html_writer::end_div();

    // Progress bar
    if ($stats->total > 0) {
        echo html_writer::start_div('progress mb-3', ['style' => 'height: 12px;']);
        $learnedpct = ($stats->learned / $stats->total * 100);
        $openpct    = ($stats->open    / $stats->total * 100);
        $errorpct   = ($stats->errors  / $stats->total * 100);
        echo html_writer::div('', 'progress-bar bg-success',
            ['style' => "width:{$learnedpct}%", 'title' => get_string('learned', 'mod_leitnerflow')]);
        echo html_writer::div('', 'progress-bar bg-warning',
            ['style' => "width:{$openpct}%", 'title' => get_string('open', 'mod_leitnerflow')]);
        echo html_writer::div('', 'progress-bar bg-danger',
            ['style' => "width:{$errorpct}%", 'title' => get_string('witherrors', 'mod_leitnerflow')]);
        echo html_writer::end_div();
    }

    // Box distribution
    $boxdist = leitner_engine::get_box_distribution(
        $leitnerflow->id, $USER->id, $leitnerflow->questioncategoryid, $leitnerflow->boxcount
    );
    echo html_writer::start_div('d-flex gap-2 mb-3 flex-wrap');
    for ($b = 1; $b <= (int)$leitnerflow->boxcount; $b++) {
        $badgeclass = ($b === 1) ? 'bg-danger' : (($b === $leitnerflow->boxcount) ? 'bg-warning' : 'bg-secondary');
        echo html_writer::span(
            "Box {$b}: " . ($boxdist[$b] ?? 0),
            'badge ' . $badgeclass . ' text-white'
        );
    }
    echo html_writer::end_div();

    echo html_writer::end_div(); // card-body
    echo html_writer::end_div(); // card

    // Action button
    if ($stats->total === 0) {
        echo $OUTPUT->notification(get_string('nocardsinpool', 'mod_leitnerflow'), 'warning');
    } elseif ($stats->learned >= $stats->total) {
        echo $OUTPUT->notification(get_string('alllearned', 'mod_leitnerflow'), 'success');
    } elseif ($activesession) {
        $continueurl = new moodle_url('/mod/leitnerflow/attempt.php', [
            'id'     => $cm->id,
            'sessid' => $activesession->id,
        ]);
        echo html_writer::div(
            $OUTPUT->single_button($continueurl, get_string('continuesession', 'mod_leitnerflow'), 'get',
                ['class' => 'btn-primary']),
            'mb-3'
        );
    } else {
        $starturl = new moodle_url('/mod/leitnerflow/attempt.php', ['id' => $cm->id, 'start' => 1]);
        echo html_writer::div(
            $OUTPUT->single_button($starturl, get_string('startsession', 'mod_leitnerflow'), 'get',
                ['class' => 'btn-primary']),
            'mb-3'
        );
    }
}

// ---- Teacher view ----------------------------------------------------------
if ($isteacher) {
    $reporturl = new moodle_url('/mod/leitnerflow/report.php', ['id' => $cm->id]);
    echo html_writer::div(
        $OUTPUT->single_button($reporturl, get_string('viewreport', 'mod_leitnerflow'), 'get'),
        'mt-2'
    );
}

echo $OUTPUT->footer();
