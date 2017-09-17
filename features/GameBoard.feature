Feature: Game Board (Board in short) is the full view of currently played Game

  Scenario: Board is not available for anonymous User
    When I am Anonymous User
    And Game with GCode "abc" exists
    And I go to "/pl/board/abc"
    Then I should be on "/pl/v2.0/dialog/oauth"

#  Scenario: Board is available for User who created the Game
#    When I am authenticated as "damian"
#    And I create a Game
#    And I open "/board" on "default" device
#    Then I should be on "/board"
#    And I should see "damian"

#  Scenario: Game owner can remove other Users from the Game
#    // TODO: implement - will require JS support?
#    When I am authenticated as "damian"
#    And I create a Game
#    And "agata" joins my Game
#    And I remove User "agata" from my Game
#    Then I should not see "agata"

#  Scenario: When Game owner logs out Game is being removed
#    // TODO: implement
#    When I am authenticated as "damian"
#    And I create a Game
#    And I log out
#    Then User "damian" should own "0" Games

#  Scenario: Board lets to end the Game but only User that created the Game
#    // TODO: implement
#
#  Scenario: Board lets to reset the Game but only User that created the Game
#    // TODO: implement
