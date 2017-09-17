Feature: Every Game has it's "Code" (called GCode), eg. "dkev" (a couple of random chars)
  GCode is used to quickly, uniquely identify and join to existing Game

  Scenario: Game Board shows GCode
    Given I am authenticated as "damian"
    When I create a Game
    Then I should see "Kod gry" in the "body *" element

  Scenario: Anonymous User can join a Game from homepage
    Given I am Anonymous User using "Controller" device
    And Game with GCode "abc" exists
    When I am on homepage
    And I fill in "gcode" with "abc"
    And I press "button_play"
    And I fill in "display_name" with "Jacek"
    And I press "submit"
    Then I should see "Link dla znajomego" in the "body *" element
    And I should see "abc" in the "body *" element
    And I should see "Jacek" in the "body *" element