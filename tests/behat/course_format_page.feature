@course @course_format_page
Feature: simplepage tests
  In order to test simplepage functionality
  As admin
  I need to start these tests

  @javascript
  Scenario: create new simplepage course
    Given I log in as "admin"
    When I follow "Courses"
    And I press "Add a new course"
    And I set the field "id_fullname" to "course 1"
    And I set the field "id_shortname" to "course1"
    And I set the field "id_format" to "simplepage"
    And I press "submitbutton"
    And I follow "course 1"
    Then I should see "The current course is under construction. Please, come back later."
