<?php
/**
 * Attempt page: renders one question at a time using Moodle's question_engine.
 * Flow:
 *   GET  ?id=cmid&start=1      → create new session, redirect to first question
 *   GET  ?id=cmid&sessid=X     → show current question (slot = session->currentindex + 1)
 *   POST ?id=cmid&sessid=X     → process answer, update Leitner state, redirect to next question
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');

use mod_leitnerflow\engine\leitner_engine;

$cmid   = required_param('id',    PARAM_INT);
$sessid = optional_param('sessid', 0,  PARAM_INT);
$start  = optional_param('start',  0,  PARAM_INT);

$cm          = get_coursemodule_from_id('leitnerflow', $cmid, 0, false, MUST_EXIST);
$course      = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$leitnerflow = $DB->get_record('leitnerflow', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
require_capability('mod/leitnerflow:attempt', \core\context\module::instance($cm->id));

$context = \core\context\module::instance($cm->id);
$viewurl = new moodle_url('/mod/leitnerflow/view.php', ['id' => $cm->id]);

// ---- Start a new session ---------------------------------------------------
if ($start) {
    // Cancel any stale active session
    $stale = $DB->get_records('leitnerflow_sessions', [
        'leitnerflowid' => $leitnerflow->id,
        'userid'        => $USER->id,
        'status'        => 0,
    ]);
    foreach ($stale as $s) {
        if (!empty($s->qubaid)) {
            question_engine::delete_questions_usage_by_activity($s->qubaid);
        }
    }
    $DB->delete_records('leitnerflow_sessions', [
        'leitnerflowid' => $leitnerflow->id,
        'userid'        => $USER->id,
        'status'        => 0,
    ]);

    // Select questions for this session
    $questionids = leitner_engine::select_session_questions($leitnerflow, $USER->id);

    if (empty($questionids)) {
        redirect($viewurl, get_string('nounlearnedcards', 'mod_leitnerflow'),
            null, \core\output\notification::NOTIFY_INFO);
    }

    // Build question_usage_by_activity (quba)
    $quba = question_engine::make_questions_usage_by_activity('mod_leitnerflow', $context);
    $quba->set_preferred_behaviour('immediatefeedback');

    foreach ($questionids as $qid) {
        $question = question_bank::load_question($qid);
        $quba->add_question($question, 1);
    }
    $quba->start_all_questions();
    question_engine::save_questions_usage_by_activity($quba);

    // Save session record
    $session = new stdClass();
    $session->leitnerflowid  = $leitnerflow->id;
    $session->userid         = $USER->id;
    $session->qubaid         = $quba->get_id();
    $session->questionids    = json_encode($questionids);
    $session->currentindex   = 0;
    $session->questionsasked = 0;
    $session->questionscorrect = 0;
    $session->status         = 0;
    $session->timecreated    = time();
    $session->id = $DB->insert_record('leitnerflow_sessions', $session);

    redirect(new moodle_url('/mod/leitnerflow/attempt.php', ['id' => $cmid, 'sessid' => $session->id]));
}

// ---- Load existing session -------------------------------------------------
if (!$sessid) {
    redirect($viewurl);
}
$session = $DB->get_record('leitnerflow_sessions', ['id' => $sessid, 'userid' => $USER->id], '*', MUST_EXIST);

if ((int)$session->status === 1) {
    // Already completed
    redirect($viewurl);
}

$questionids = json_decode($session->questionids, true);
$totalquestions = count($questionids);
$currentindex   = (int)$session->currentindex;

// Session complete?
if ($currentindex >= $totalquestions) {
    _leitnerflow_finish_session($session, $leitnerflow, $cm, $course);
    exit;
}

$quba = question_engine::load_questions_usage_by_activity($session->qubaid);
$slot = $currentindex + 1; // quba slots are 1-based

// ---- Process POST (answer submission) --------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_sesskey();

    $timenow = time();
    $quba->process_all_actions($timenow);
    question_engine::save_questions_usage_by_activity($quba);

    // Was the answer correct?
    $fraction = $quba->get_question_fraction($slot);
    $correct  = ($fraction !== null && $fraction >= 1.0);

    // Update Leitner state
    $questionid = $questionids[$currentindex];
    $state = leitner_engine::get_card_state($leitnerflow->id, $USER->id, $questionid);
    $state = leitner_engine::process_answer($state, $correct, $leitnerflow, $questionid, $USER->id);
    leitner_engine::save_card_state($state);

    // Update session progress
    $session->questionsasked++;
    if ($correct) {
        $session->questionscorrect++;
    }
    $session->currentindex = $currentindex + 1;

    if ($session->currentindex >= $totalquestions) {
        // Session done
        _leitnerflow_finish_session($session, $leitnerflow, $cm, $course);
        exit;
    }

    $DB->update_record('leitnerflow_sessions', $session);

    redirect(new moodle_url('/mod/leitnerflow/attempt.php', ['id' => $cmid, 'sessid' => $sessid]));
}

// ---- Render the current question -------------------------------------------
$PAGE->set_url('/mod/leitnerflow/attempt.php', ['id' => $cmid, 'sessid' => $sessid]);
$PAGE->set_title(format_string($leitnerflow->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->add_body_class('mod-leitnerflow-attempt');
$PAGE->requires->js_call_amd('mod_leitnerflow/quiz_session', 'init');

// Display options for question rendering
$displayoptions = new question_display_options();
$displayoptions->marks              = question_display_options::MAX_ONLY;
$displayoptions->feedback           = question_display_options::HIDDEN;
$displayoptions->generalfeedback    = question_display_options::HIDDEN;
$displayoptions->rightanswer        = question_display_options::HIDDEN;
$displayoptions->history            = question_display_options::HIDDEN;
$displayoptions->correctness        = question_display_options::HIDDEN;

// Get card state for display
$questionid = $questionids[$currentindex];
$cardstate  = leitner_engine::get_card_state($leitnerflow->id, $USER->id, $questionid);
$currentbox = $cardstate ? (int)$cardstate->currentbox : 1;
$correctcount = $cardstate ? (int)$cardstate->correctcount : 0;

echo $OUTPUT->header();

// Progress indicator
$progresspct = round(($currentindex / $totalquestions) * 100);
echo html_writer::start_div('leitnerflow-attempt-header mb-4');
echo html_writer::start_div('d-flex justify-content-between align-items-center mb-2');
echo html_writer::span(
    get_string('question', 'mod_leitnerflow') . ' ' .
    ($currentindex + 1) . ' ' .
    get_string('of', 'mod_leitnerflow') . ' ' .
    $totalquestions,
    'text-muted small'
);
// Box badge
$boxlabel = ($cardstate && (int)$cardstate->status === leitner_engine::STATUS_LEARNED)
    ? get_string('cardstatus_learned', 'mod_leitnerflow')
    : get_string('cardstatus_box', 'mod_leitnerflow', $currentbox);
echo html_writer::span($boxlabel, 'badge bg-secondary');
echo html_writer::end_div();

// Progress bar
echo html_writer::start_div('progress mb-3', ['style' => 'height: 8px;']);
echo html_writer::div('', 'progress-bar bg-primary',
    ['style' => "width:{$progresspct}%", 'role' => 'progressbar',
     'aria-valuenow' => $progresspct, 'aria-valuemin' => 0, 'aria-valuemax' => 100]);
echo html_writer::end_div();

// Leitner box visualization
echo html_writer::start_div('leitnerflow-boxes d-flex gap-1 mb-3');
for ($b = 1; $b <= (int)$leitnerflow->boxcount; $b++) {
    $active = ($b === $currentbox);
    $boxclass = 'leitnerflow-box badge ' . ($active ? 'bg-primary' : 'bg-light text-dark border');
    echo html_writer::span("□ {$b}", $boxclass);
}
$needmore = (int)$leitnerflow->correcttolearn - $correctcount;
echo html_writer::span(
    "{$correctcount} / {$leitnerflow->correcttolearn} ✓",
    'ms-2 small text-muted'
);
echo html_writer::end_div();
echo html_writer::end_div(); // attempt-header

// Question form
$actionurl = new moodle_url('/mod/leitnerflow/attempt.php', ['id' => $cmid, 'sessid' => $sessid]);
echo html_writer::start_tag('form', [
    'method'  => 'post',
    'action'  => $actionurl->out(false),
    'enctype' => 'multipart/form-data',
    'class'   => 'leitnerflow-question-form',
    'id'      => 'leitnerflow-question-form',
]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()]);
echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'attempt', 'value' => 1]);

// Render the question itself
echo html_writer::start_div('leitnerflow-question-container card mb-3');
echo html_writer::start_div('card-body');
echo $quba->render_question($slot, $displayoptions, ($currentindex + 1) . '');
echo html_writer::end_div();
echo html_writer::end_div();

// Submit button
echo html_writer::start_div('d-grid gap-2 d-sm-flex');
echo html_writer::tag('button', get_string('check', 'mod_leitnerflow'), [
    'type'  => 'submit',
    'class' => 'btn btn-primary btn-lg flex-fill',
    'id'    => 'leitnerflow-check-btn',
]);
echo html_writer::end_div();

echo html_writer::end_tag('form');

// Back to overview link
echo html_writer::div(
    html_writer::link($viewurl, get_string('modulenameplural', 'mod_leitnerflow'), ['class' => 'text-muted small']),
    'mt-3 text-center'
);

echo $OUTPUT->footer();

// ---- Helper: finish session ------------------------------------------------
function _leitnerflow_finish_session(stdClass $session, stdClass $leitnerflow, stdClass $cm, stdClass $course): void {
    global $DB, $OUTPUT, $PAGE, $USER;

    $session->status        = 1;
    $session->timecompleted = time();
    $DB->update_record('leitnerflow_sessions', $session);

    // Update gradebook
    if ((int)$leitnerflow->grademethod > 0) {
        leitnerflow_update_grades($leitnerflow, $USER->id);
    }

    $viewurl = new moodle_url('/mod/leitnerflow/view.php', ['id' => $cm->id]);

    $PAGE->set_url('/mod/leitnerflow/attempt.php', ['id' => $cm->id]);
    $PAGE->set_title(format_string($leitnerflow->name));
    $PAGE->set_context(\core\context\module::instance($cm->id));
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('sessioncomplete', 'mod_leitnerflow'), 3);

    $result = get_string('sessionresult', 'mod_leitnerflow', [
        'correct' => $session->questionscorrect,
        'total'   => $session->questionsasked,
    ]);
    echo $OUTPUT->notification($result, 'success');

    echo $OUTPUT->single_button($viewurl, get_string('continue'), 'get');
    echo $OUTPUT->footer();
}
