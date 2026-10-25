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
 * Teacher report page for mod_eledialeitnerflow.
 *
 * Overview of all enrolled students' Leitner progress with pagination,
 * search, and sortable columns — suitable for courses with 100+ participants.
 *
 * @package    mod_eledialeitnerflow
 * @copyright  2026 eLeDia GmbH
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

use mod_eledialeitnerflow\engine\leitner_engine;

$cmid      = required_param('id', PARAM_INT);
$resetuid  = optional_param('resetuserid', 0, PARAM_INT);
$page      = optional_param('page', 0, PARAM_INT);
$perpage   = optional_param('perpage', 25, PARAM_INT);
$tsort     = optional_param('tsort', 'fullname', PARAM_ALPHA);
$tdir      = optional_param('tdir', 'ASC', PARAM_ALPHA);
$search    = optional_param('search', '', PARAM_TEXT);

// Sanitise sort direction.
$tdir = (strtoupper($tdir) === 'DESC') ? 'DESC' : 'ASC';

// Allowed sort columns.
$allowedsorts = ['fullname', 'learned', 'progress', 'sessions', 'lastsession'];
if (!in_array($tsort, $allowedsorts)) {
    $tsort = 'fullname';
}

$cm          = get_coursemodule_from_id('eledialeitnerflow', $cmid, 0, false, MUST_EXIST);
$course      = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
$leitnerflow = $DB->get_record('eledialeitnerflow', ['id' => $cm->instance], '*', MUST_EXIST);

require_login($course, true, $cm);
$context = \core\context\module::instance($cm->id);
require_capability('mod/eledialeitnerflow:viewreport', $context);

// Handle reset-progress request for a single student.
if ($resetuid > 0) {
    require_sesskey();
    require_capability('mod/eledialeitnerflow:resetprogress', $context);
    leitner_engine::delete_user_data($leitnerflow->id, $resetuid);

    $event = \mod_eledialeitnerflow\event\progress_reset::create([
        'objectid'      => $leitnerflow->id,
        'context'       => $context,
        'relateduserid' => $resetuid,
    ]);
    $event->trigger();

    redirect(
        new moodle_url('/mod/eledialeitnerflow/report.php', ['id' => $cmid]),
        get_string('progressreset', 'mod_eledialeitnerflow'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Build the base URL and set up the page.
$baseurl = new moodle_url('/mod/eledialeitnerflow/report.php', [
    'id' => $cmid, 'tsort' => $tsort, 'tdir' => $tdir, 'perpage' => $perpage, 'search' => $search,
]);

$PAGE->set_url('/mod/eledialeitnerflow/report.php', ['id' => $cmid]);
$PAGE->set_title(format_string($leitnerflow->name) . ': ' . get_string('report', 'mod_eledialeitnerflow'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);
$PAGE->activityheader->set_description('');

$viewurl = new moodle_url('/mod/eledialeitnerflow/view.php', ['id' => $cmid]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('report', 'mod_eledialeitnerflow'), 3);

// Fetch aggregated stats for all enrolled students.
$students = leitner_engine::get_all_students_stats($leitnerflow, $course->id, $context);

$hasstartedstudents = array_filter($students, fn($s) => $s->sessions > 0);
if (empty($hasstartedstudents)) {
    echo $OUTPUT->notification(get_string('nostudents', 'mod_eledialeitnerflow'), 'info', false);
    echo $OUTPUT->single_button($viewurl, get_string('back'), 'get');
    echo $OUTPUT->footer();
    exit;
}

// Apply the text search filter on student names.
if ($search !== '') {
    $searchlower = core_text::strtolower($search);
    $students = array_filter($students, function ($s) use ($searchlower) {
        return str_contains(core_text::strtolower($s->fullname), $searchlower);
    });
}

// Sort by the selected column and direction.
usort($students, function ($a, $b) use ($tsort, $tdir) {
    switch ($tsort) {
        case 'learned':
            $cmp = $a->stats->learned <=> $b->stats->learned;
            break;
        case 'progress':
            $cmp = $a->stats->percent_learned <=> $b->stats->percent_learned;
            break;
        case 'sessions':
            $cmp = $a->sessions <=> $b->sessions;
            break;
        case 'lastsession':
            $cmp = ($a->lastsession ?? 0) <=> ($b->lastsession ?? 0);
            break;
        default: // Fullname.
            $cmp = strcmp(
                core_text::strtolower($a->fullname),
                core_text::strtolower($b->fullname)
            );
    }
    return ($tdir === 'DESC') ? -$cmp : $cmp;
});

$totalcount    = count($students);
$allstudents   = $students; // Keep full set for summary cards.
$students      = array_slice($students, $page * $perpage, $perpage);

// Summary cards: aggregate stats across all students.
$totallearned = 0;
$totalcards   = 0;
foreach ($allstudents as $s) {
    $totallearned += $s->stats->learned;
    $totalcards   += $s->stats->total;
}
$avgpct = $totalcount > 0
    ? round(array_sum(array_map(fn($s) => $s->stats->percent_learned, $allstudents)) / $totalcount)
    : 0;

$questioncount = !empty($allstudents) ? reset($allstudents)->stats->total : 0;

echo html_writer::start_div('row mb-4');
$summaries = [
    [get_string('participants'), $totalcount],
    [get_string('questionsinpool', 'mod_eledialeitnerflow'), $questioncount],
    [get_string('avglearnedpercent', 'mod_eledialeitnerflow'), $avgpct . ' %'],
];
foreach ($summaries as $sum) {
    echo html_writer::start_div('col-md-4 mb-2');
    echo html_writer::start_div('card h-100');
    echo html_writer::start_div('card-body text-center py-2');
    echo html_writer::tag('div', $sum[1], ['class' => 'eledialeitnerflow-kpi-value']);
    echo html_writer::tag('div', $sum[0], ['class' => 'small text-muted']);
    echo html_writer::end_div();
    echo html_writer::end_div();
    echo html_writer::end_div();
}
echo html_writer::end_div();

// Search (core search input) and per-page selector (core single select).
$searchurl = new moodle_url('/mod/eledialeitnerflow/report.php');
echo html_writer::start_div('d-flex gap-2 mb-3 align-items-center flex-wrap');
echo $OUTPUT->render_from_template('core/search_input', [
    'action' => $searchurl->out(false),
    'inputname' => 'search',
    'query' => $search,
    'searchstring' => get_string('searchparticipants', 'mod_eledialeitnerflow'),
    'hiddenfields' => [
        ['name' => 'id', 'value' => $cmid],
        ['name' => 'tsort', 'value' => $tsort],
        ['name' => 'tdir', 'value' => $tdir],
        ['name' => 'perpage', 'value' => $perpage],
    ],
]);
if ($search !== '') {
    $clearurl = new moodle_url('/mod/eledialeitnerflow/report.php', [
        'id' => $cmid, 'tsort' => $tsort, 'tdir' => $tdir, 'perpage' => $perpage,
    ]);
    echo html_writer::link($clearurl, get_string('clear'), ['class' => 'btn btn-link']);
}
$perpageurl = new moodle_url('/mod/eledialeitnerflow/report.php', [
    'id' => $cmid, 'tsort' => $tsort, 'tdir' => $tdir, 'search' => $search,
]);
$perpageoptions = [10 => '10', 25 => '25', 50 => '50', 100 => '100'];
echo html_writer::div(
    $OUTPUT->single_select($perpageurl, 'perpage', $perpageoptions, $perpage, null, null, [
        'label' => get_string('perpage', 'moodle', ''),
    ]),
    'ms-auto small'
);
echo html_writer::end_div();

/**
 * Build a sort link for a table header.
 *
 * @param string $column   Column key.
 * @param string $label    Display label.
 * @param string $tsort    Current sort column.
 * @param string $tdir     Current sort direction.
 * @param int    $cmid     Course module id.
 * @param int    $perpage  Items per page.
 * @param string $search   Current search term.
 * @return string HTML link.
 */
function _eledialeitnerflow_sort_link(
    string $column,
    string $label,
    string $tsort,
    string $tdir,
    int $cmid,
    int $perpage,
    string $search
): string {
    $newdir = ($tsort === $column && $tdir === 'ASC') ? 'DESC' : 'ASC';
    $url = new moodle_url('/mod/eledialeitnerflow/report.php', [
        'id' => $cmid, 'tsort' => $column, 'tdir' => $newdir, 'perpage' => $perpage, 'search' => $search,
    ]);
    $arrow = '';
    if ($tsort === $column) {
        $icon = ($tdir === 'ASC') ? 'fa-sort-up' : 'fa-sort-down';
        $arrow = ' ' . html_writer::tag('i', '', ['class' => 'fa ' . $icon, 'aria-hidden' => 'true']);
    }
    return html_writer::link($url, $label . $arrow, ['class' => 'text-nowrap']);
}

// Render the student table.
echo html_writer::start_div('table-responsive');
echo html_writer::start_tag('table', ['class' => 'generaltable table table-sm']);
echo html_writer::start_tag('thead');
echo html_writer::start_tag('tr');
$headers = [
    ['key' => 'fullname', 'label' => get_string('participants')],
    ['key' => 'learned', 'label' => get_string('learned', 'mod_eledialeitnerflow')],
    ['key' => '', 'label' => get_string('open', 'mod_eledialeitnerflow')],
    ['key' => '', 'label' => get_string('witherrors', 'mod_eledialeitnerflow')],
    ['key' => 'progress', 'label' => get_string('progress')],
    ['key' => 'sessions', 'label' => get_string('sessionhistory', 'mod_eledialeitnerflow')],
    ['key' => 'lastsession', 'label' => get_string('lastsession', 'mod_eledialeitnerflow')],
    ['key' => '', 'label' => get_string('actions')],
];
$numericcols = ['learned', 'sessions'];
foreach ($headers as $i => $h) {
    $thattrs = ['scope' => 'col'];
    if (in_array($h['key'], $numericcols, true) || $i === 2 || $i === 3) {
        $thattrs['class'] = 'text-end';
    }
    if (!empty($h['key'])) {
        if ($tsort === $h['key']) {
            $thattrs['aria-sort'] = ($tdir === 'ASC') ? 'ascending' : 'descending';
        }
        echo html_writer::tag(
            'th',
            _eledialeitnerflow_sort_link($h['key'], $h['label'], $tsort, $tdir, $cmid, $perpage, $search),
            $thattrs
        );
    } else {
        echo html_writer::tag('th', $h['label'], $thattrs);
    }
}
echo html_writer::end_tag('tr');
echo html_writer::end_tag('thead');
echo html_writer::start_tag('tbody');

foreach ($students as $student) {
    $stats = $student->stats;
    $pct   = $stats->percent_learned;

    $progressbar = html_writer::start_div('progress eledialeitnerflow-progress--report flex-grow-1', [
        'role' => 'progressbar',
        'aria-valuenow' => $pct,
        'aria-valuemin' => 0,
        'aria-valuemax' => 100,
        'aria-label' => get_string('progress') . ': ' . $student->fullname,
    ]);
    $progressbar .= html_writer::div('', 'progress-bar eledialeitnerflow-seg-learned', ['style' => "width:{$pct}%"]);
    $progressbar .= html_writer::end_div();

    $lastsessionstr = $student->lastsession
        ? userdate($student->lastsession, get_string('strftimedate', 'langconfig'))
        : '—';

    $reseturl = new moodle_url('/mod/eledialeitnerflow/report.php', [
        'id'          => $cmid,
        'resetuserid' => $student->userid,
        'sesskey'     => sesskey(),
    ]);

    $resetbtn = '';
    if (has_capability('mod/eledialeitnerflow:resetprogress', $context)) {
        $resetbtn = html_writer::link(
            $reseturl,
            get_string('resetprogress', 'mod_eledialeitnerflow'),
            [
                'class'        => 'btn btn-sm btn-outline-danger',
                'data-confirm' => get_string('resetconfirm', 'mod_eledialeitnerflow', $student->fullname),
            ]
        );
    }

    echo html_writer::start_tag('tr');
    $userpic = $OUTPUT->user_picture($student->user, ['size' => 24]);
    $profileurl = new moodle_url('/user/view.php', ['id' => $student->userid, 'course' => $course->id]);
    echo html_writer::tag(
        'td',
        $userpic . ' ' . html_writer::link($profileurl, $student->fullname)
    );
    echo html_writer::tag('td', $stats->learned, ['class' => 'text-end']);
    echo html_writer::tag('td', $stats->open, ['class' => 'text-end']);
    echo html_writer::tag('td', $stats->errors, ['class' => 'text-end']);
    echo html_writer::tag(
        'td',
        html_writer::div($progressbar . html_writer::span("{$pct} %", 'small text-nowrap'), 'd-flex align-items-center gap-2')
    );
    echo html_writer::tag('td', $student->sessions, ['class' => 'text-end']);
    echo html_writer::tag('td', $lastsessionstr);
    echo html_writer::tag('td', $resetbtn);
    echo html_writer::end_tag('tr');
}

echo html_writer::end_tag('tbody');
echo html_writer::end_tag('table');
echo html_writer::end_div(); // Table-responsive.

// Paging bar.
echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);

// Back-to-view button.
echo html_writer::div(
    $OUTPUT->single_button($viewurl, get_string('back'), 'get'),
    'mt-3'
);

// Confirm-dialog JS for reset buttons.
$PAGE->requires->js_call_amd('mod_eledialeitnerflow/confirm_reset', 'init');

echo $OUTPUT->footer();
