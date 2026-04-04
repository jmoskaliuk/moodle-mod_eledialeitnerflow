@mod @mod_eledialeitnerflow
Feature: Student attempts a LeitnerFlow study session
  As a student
  I need to start and complete study sessions
  So that I can learn through spaced repetition

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Student   | One      | student1@example.com |
      | teacher1 | Teacher   | One      | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name     |
      | Course       | C1        | TestCat1 |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext    | answer 1 | grade |
      | TestCat1         | truefalse | Q1   | The sky is blue | True     | 100   |
      | TestCat1         | truefalse | Q2   | 2+2=5           | False    | 100   |
      | TestCat1         | truefalse | Q3   | Fire is hot     | True     | 100   |
    And the following "activities" exist:
      | activity          | name        | course | idnumber | questioncategoryid | sessionsize | feedbackstyle |
      | eledialeitnerflow | Test Leitner| C1     | lf1      | TestCat1           | 3           | 0             |

  @javascript
  Scenario: Student starts a study session
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test Leitner"
    When I click on "Start study session" "link"
    Then I should see "Question 1 / 3"
    And I should see "Back to overview"
    And I should see "End session"

  @javascript
  Scenario: Student can navigate back to overview during a session
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test Leitner"
    And I click on "Start study session" "link"
    When I click on "Back to overview" "button"
    Then I should see "My learning progress"
    And I should see "Continue session"

  @javascript
  Scenario: Student can end a session early
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test Leitner"
    And I click on "Start study session" "link"
    When I click on "End session" "button"
    Then I should see "Session cancelled"
    And I should see "Start study session"
