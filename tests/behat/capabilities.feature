@mod @mod_eledialeitnerflow
Feature: LeitnerFlow capability checks
  As an administrator
  I need capabilities to control who can do what
  So that roles are properly enforced

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |

  @javascript
  Scenario: Editing teacher can add a LeitnerFlow activity
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I open the activity chooser
    Then I should see "LeitnerFlow"

  @javascript
  Scenario: Student cannot add a LeitnerFlow activity
    Given I log in as "student1"
    When I am on "Course 1" course homepage
    Then I should not see "Turn editing on"
