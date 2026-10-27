@local @local_manaselabproject
Feature: The lead of a project edits its profile from a phone
  In order to keep the profile of my project up to date
  As the lead of a project
  I need a short form that works on a phone and never takes me to the course settings

  Background:
    Given the following "custom field categories" exist:
      | name        | component   | area   | itemid |
      | Manas E-Lab | core_course | course | 0      |
    And the following "custom fields" exist:
      | name          | category    | type   | shortname    | configdata                                                     |
      | Space type    | Manas E-Lab | select | spacetype    | {"options":"call\ntraining\nproject","locked":"1"}             |
      | Call status   | Manas E-Lab | select | callstatus   | {"options":"draft\nopen","locked":"1"}                         |
      | Project stage | Manas E-Lab | select | projectstage | {"options":"stage1_idea\nstage2_validation\nstage3_mvp"}       |
      | Project need  | Manas E-Lab | text   | projectneed  | {"displaysize":50,"maxlength":1333}                            |
    And the following "roles" exist:
      | shortname     | name         | archetype |
      | projectlead   | Project lead | student   |
      | projectmember | Member       | student   |
    And the following "permission overrides" exist:
      | capability                         | permission | role        | contextlevel | reference |
      | local/manaselabproject:editproject | Allow      | projectlead | System       |           |
    And the following "users" exist:
      | username | firstname | lastname |
      | lead1    | Lead      | One      |
      | member1  | Member    | One      |
    And the following "courses" exist:
      | fullname     | shortname | customfield_spacetype | customfield_projectstage |
      | Solar dryers | SD        | 3                     | 1                        |
    And the following "course enrolments" exist:
      | user    | course | role          |
      | lead1   | SD     | projectlead   |
      | member1 | SD     | projectmember |

  @javascript
  Scenario: The lead changes the stage and the need of the project on a phone
    Given I change the viewport size to "mobile"
    And I am on the "Solar dryers" "local_manaselabproject > Edit project" page logged in as "lead1"
    Then I should see "Edit my project"
    And I should not see "Space type"
    And I should not see "Call status"
    And I should not see "Course full name"
    When I set the following fields to these values:
      | Project stage | Stage 2 · Validation |
      | Project need  | Looking for a mentor |
    And I press "Save"
    Then I should see "Your project has been updated."
    And I am on the "Solar dryers" "local_manaselabproject > Edit project" page
    And the following fields match these values:
      | Project stage | Stage 2 · Validation |
      | Project need  | Looking for a mentor |

  Scenario: A member of the project gets no link and cannot open the form
    Given I am on the "Solar dryers" "Course" page logged in as "member1"
    Then I should not see "Edit my project"
    And I am on the "Solar dryers" "local_manaselabproject > Edit project" page
    And I should see "Sorry, but you do not currently have permissions to do that"
