Feature: Game Watcher is a screen that can be used by User to show his Game (Game's Board)

  Scenario: Game Watcher is available for anonymous User
    Given There are no Games
    When I am Anonymous User
    And I go to "/pl/watcher"
    Then I should see button "watch-button" with text "Obserwuj grę"

  Scenario: Game Watcher redirects to LAN Game
    Given LAN Game with GCode "abc" exists
    When I am Anonymous User using "Board" device
    And I go to "/pl/watcher"
    Then I should see "Game-Watcher[GCode=abc]" on "Board" device

  Scenario: Game Watcher redirects to LAN Game
    Given There are no Games
    And Game with GCode "abc" exists
    When I am Anonymous User using "Board" device
    And I go to "/pl/watcher"
    And I fill in "gcode" with "abc"
    And I press "Obserwuj grę"
    Then I should see "Game-Watcher[GCode=abc]" on "Board" device
