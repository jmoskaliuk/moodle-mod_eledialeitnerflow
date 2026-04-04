@mod @mod_eledialeitnerflow
Feature: Student views LeitnerFlow activity
  As a student
  I need to see my learning progress and box distribution
  So that I know how I am doing

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
    And the following "question categories" exist:
      | contextlevel | reference | name     |
      | Course       | C1        | TestCat1 |
    And the following "questions" exist:
      | questioncategory | qtype     | name | questiontext    | answer 1 | grade |
      | TestCat1         | truefalse | Q1   | The sky is blue | True     | 100   |
      | TestCat1         | truefalse | Q2   | 2+2=5           | False    | 100   |
      | TestCat1         | truefalse | Q3   | Fire is hot     | True     | 100   |
    And the following "activities" exist:
      | activity          | name        | course | idnumber | questioncategoryid |
      | eledialeitnerflow | Test Leitner| C1     | lf1      | TestCat1           |

  @javascript
  Scenario: Student sees the main view page with progress dashboard
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Leitner"
    Then I should see "My learning progress"
    And I should see "Start study session"
    And I should see "Box 1"

  @javascript
  Scenario: Student without questions sees warning message
    Given the following "activities" exist:
      | activity          | name         | course | idnumber |
      | eledialeitnerflow | Empty Leitner| C1     | lf2      |
    And I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Empty Leitner"
    Then I should see "No questions found in the selected category"

  @javascript
  Scenario: Student does not see the teacher report button
    Given I log in as "student1"
    And I am on "Course 1" course homepage
    When I follow "Test Leitner"
    Then I should not see "All participants overview"
