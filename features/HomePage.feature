Feature: Home Page is the starting point of all User's actions.
  It must let User to join and create Game in very easy and fast way.

  Scenario: Join Game as Authenticated User
    Given I am authenticated as "damian"
    When I am on the homepage
    Then I should see "Graj" in the "body *" element
    And the response status code should be 200

  Scenario: Anonymous User can access homepage
    Given I am Anonymous User
    When I am on homepage
    Then I should see "Graj" in the "body *" element

  Scenario: Anonymous User can see local Games on the Board
    Given LAN Game with GCode "abc" exists
    And I am Anonymous User using "Controller" device
    When I am on homepage
    Then I should see button "button_game_join_abc" with text "abc"

  Scenario: Anonymous User gets to Game-Join-Anonymous using QR Code on the Board
    Given Game with GCode "abc" exists
    And "Board" device displays "Game-Board[GCode=abc]"
    And I am Anonymous User using "Controller" device
    When I scan QR Code "qrcode-join" on "Board" device using "Controller" device
    Then I should see "Game-Join-Anonymous[GCode=abc]" on "Controller" device

  @Challenges
  Scenario: Home Page shows two, latest challenges
    Given I am on homepage
    Then I should see "wyzwanie_1"
    And I should see "wyzwanie_2"