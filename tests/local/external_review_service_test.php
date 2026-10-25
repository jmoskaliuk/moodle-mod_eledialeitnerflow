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
 * Standalone external-result contract with a native test-only source.
 * @covers \mod_eledialeitnerflow\local\external_review_service
 */
#[\PHPUnit\Framework\Attributes\CoversClass(external_review_service::class)]
final class external_review_service_test extends \core_privacy\tests\provider_testcase {
    /**
     * @var \stdClass Target.
     */
    private $target;
    /**
     * @var \stdClass Source.
     */
    private $source;
    /**
     * @var \stdClass User.
     */
    private $student;
    /**
     * @var \question_usage_by_activity Engine usage.
     */
    private $usage;
    /**
     * Create real activities and an ungraded usage owned by a test source.
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();
        global $CFG, $DB;
        require_once($CFG->dirroot . '/question/engine/lib.php');
        require_once(__DIR__ . '/../fixtures/external_source.php');
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $this->student = $generator->create_and_enrol($course, 'student');
        $qgen = $generator->get_plugin_generator('core_question');
        $category = $qgen->create_question_category(['contextid' => \context_course::instance($course->id)->id]);
        $question = $qgen->create_question('truefalse', null, ['category' => $category->id]);
        $this->target = $generator->create_module('eledialeitnerflow', ['course' => $course->id,
            'questioncategoryid' => $category->id, 'externalenabled' => 1]);
        $this->source = $generator->create_module('eledialeitnerflow', ['course' => $course->id]);
        $this->usage = \question_engine::make_questions_usage_by_activity(
            'mod_eledialeitnerflow',
            \context_module::instance($this->source->cmid)
        );
        $this->usage->set_preferred_behaviour('immediatefeedback');
        $this->usage->add_question(\question_bank::load_question($question->id));
        $this->usage->start_all_questions();
        \question_engine::save_questions_usage_by_activity($this->usage);
        $generator->get_plugin_generator('mod_eledialeitnerflow')->create_session([
            'eledialeitnerflowid' => $this->source->id, 'userid' => $this->student->id, 'qubaid' => $this->usage->get_id()]);
        set_config('reviewsources', 'mod_eledialeitnerflow', 'mod_eledialeitnerflow');
        $this->setUser($this->student);
    }

    /**
     * Create an authorised registration.
     */
    private function register(): int {
        return external_review_service::register_attempt(
            $this->target->cmid,
            $this->source->cmid,
            'test-review-identity-0001',
            $this->usage->get_id()
        );
    }

    /**
     * Finish a real question; correctness is never supplied to the API.
     */
    private function grade(): void {
        $prefix = $this->usage->get_question_attempt(1)->get_field_prefix();
        $this->usage->process_all_actions(time(), ['slots' => '1', $prefix . 'answer' => '1', $prefix . '-submit' => 1,
            $prefix . ':sequencecheck' => $this->usage->get_question_attempt(1)->get_sequence_check_count()]);
        \question_engine::save_questions_usage_by_activity($this->usage);
    }

    public function test_final_result_is_applied_exactly_once_without_fahrup(): void {
        global $DB;
        $id = $this->register();
        $this->grade();
        $receipt = external_review_service::commit_slot($id, 1);
        $this->assertEquals($receipt, external_review_service::commit_slot($id, 1));
        $this->assertEquals(1, $DB->get_field(
            'eledialeitnerflow_card_state',
            'attemptcount',
            ['eledialeitnerflowid' => $this->target->id, 'userid' => $this->student->id]
        ));
    }

    public function test_ungraded_result_is_rejected(): void {
        $id = $this->register();
        $this->expectException(\invalid_parameter_exception::class);
        external_review_service::commit_slot($id, 1);
    }

    public function test_source_is_disabled_by_default(): void {
        set_config('reviewsources', '', 'mod_eledialeitnerflow');
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidreviewsource', 'mod_eledialeitnerflow'));
        $this->register();
    }

    public function test_target_must_explicitly_allow_external_results(): void {
        global $DB;
        $DB->set_field('eledialeitnerflow', 'externalenabled', 0, ['id' => $this->target->id]);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidreviewsource', 'mod_eledialeitnerflow'));
        $this->register();
    }

    public function test_cannot_register_an_already_graded_usage(): void {
        $this->grade();
        $this->expectException(\invalid_parameter_exception::class);
        $this->register();
    }

    public function test_other_user_cannot_commit(): void {
        $id = $this->register();
        $this->grade();
        $other = $this->getDataGenerator()->create_and_enrol(get_course($this->source->course), 'student');
        $this->setUser($other);
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidreviewsource', 'mod_eledialeitnerflow'));
        external_review_service::commit_slot($id, 1);
    }

    public function test_reset_revokes_old_results_without_replaying(): void {
        global $DB;
        $id = $this->register();
        $this->grade();
        external_review_service::commit_slot($id, 1);
        \mod_eledialeitnerflow\engine\leitner_engine::delete_user_data($this->target->id, $this->student->id);
        $this->assertEquals(0, $DB->count_records('eledialeitnerflow_card_state', ['eledialeitnerflowid' => $this->target->id]));
        $this->expectException(\moodle_exception::class);
        $this->expectExceptionMessage(get_string('invalidreviewsource', 'mod_eledialeitnerflow'));
        external_review_service::commit_slot($id, 1);
    }

    public function test_external_only_state_is_discovered_exported_and_deleted(): void {
        global $DB;
        $this->register();
        $context = \context_module::instance($this->target->cmid);
        $provider = \mod_eledialeitnerflow\privacy\provider::class;
        $contexts = $provider::get_contexts_for_userid($this->student->id);
        $this->assertContains((int)$context->id, array_map('intval', $contexts->get_contextids()));
        $approved = new \core_privacy\local\request\approved_contextlist(
            $this->student,
            'mod_eledialeitnerflow',
            [$context->id]
        );
        $provider::export_user_data($approved);
        $exported = \core_privacy\local\request\writer::with_context($context)->get_data(['externalreviews']);
        $this->assertCount(1, $exported->attempts);
        $provider::delete_data_for_user($approved);
        $this->assertEquals(0, $DB->count_records('eledialeitnerflow_ext_attempts'));
        $this->assertTrue($DB->record_exists('question_usages', ['id' => $this->usage->get_id()]));
        $this->assertTrue($DB->record_exists('eledialeitnerflow_sessions', ['eledialeitnerflowid' => $this->source->id]));
    }
}
