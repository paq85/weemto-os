@disabled
Feature: Playing multi-player Game in local area network (eg. via same WiFi) should be very easy.
  The goal is to have "one-click" actions on every device for every user.

  Scenario: Anonymous User can create a LAN Game
    Given I am Anonymous User using "Board" device
    And "Board" device displays "StartPage"
    And I press "button_play"
    And I fill in "display_name" with "Zbyszek"
    And I press "submit"
    Then I should see "Link dla znajomego" in the "body *" element
    And I should see "Zbyszek" in the "body *" element
    And Game for Session ID "127.0.0.1" should exist

  Scenario: Anonymous User can create a LAN Game
    Given There are no Games
    And I am Anonymous User using "Board" device
    And "Board" device displays "StartPage"
    When I press "button_play"
    And I fill in "display_name" with "Zbyszek"
    And I press "submit"
    Then I should see "Link dla znajomego" in the "body *" element
    And I should see "Zbyszek" in the "body *" element
    And Game for Session ID "127.0.0.1" should exist

  Scenario: Anonymous User can join LAN Game from homepage
    Given I am Anonymous User using "Controller" device
    And LAN Game with GCode "abc" exists
    When I am on homepage
    And I press "button_game_join_abc"
    And I fill in "display_name" with "Jacek"
    And I press "submit"
    Then I should see "Link dla znajomego" in the "body *" element
    And I should see "abc" in the "body *" element
    And I should see "Jacek" in the "body *" element

  Scenario: Anonymous User joins LAN Game if it exists and he requested playing with friends
    Given I am Anonymous User using "Controller" device
    And LAN Game with GCode "abc" exists
    When I am on homepage
    And I press "button_play"
    And I fill in "display_name" with "Jacek"
    And I press "submit"
    Then I should see "Link dla znajomego" in the "body *" element
    And I should see "abc" in the "body *" element
    And I should see "Jacek" in the "body *" element

  Scenario: User gets 404 if he/she tries to join non existing Game
  # // TODO: implement