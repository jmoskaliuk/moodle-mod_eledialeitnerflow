@mod @mod_eledialeitnerflow
Feature: Teacher views the participant report
  As a teacher
  I need to see all students' progress in the LeitnerFlow activity
  So that I can monitor their learning

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
      | student2 | Student   | Two      | student2@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "question categories" exist:
      | contextlevel | reference | name     |
      | Course       | C1        | TestCat1 |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext    | answer 1 | grade |
      | TestCat1         | truefalse | Q1   | The sky is blue | True     | 100   |
      | TestCat1         | truefalse | Q2   | 2+2=5           | False    | 100   |
    And the following "activities" exist:
      | activity          | name        | course | idnumber | questioncategoryid |
      | eledialeitnerflow | Test Leitner| C1     | lf1      | TestCat1           |

  @javascript
  Scenario: Teacher can access the report from the activity view
    Given a LeitnerFlow session exists for "student1" in "Test Leitner"
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    When I follow "Test Leitner"
    Then I should see "All participants overview"
    When I click on "All participants overview" "button"
    Then I should see "All participants overview"
    And I should see "Participants"
    And I should see "Questions in pool"

  @javascript
  Scenario: Teacher sees no-students message when nobody has started
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Test Leitner"
    When I click on "All participants overview" "button"
    Then I should see "No students have started this activity yet"

  @javascript
  Scenario: Student cannot access the teacher report
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Leitner"
    Then I should not see "All participants overview"
