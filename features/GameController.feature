Feature: Game Controller (Controller in short) is the partial view of currently played Game
  which lets User to provide answers using for example a Smartphone

  Scenario: Controller is not available for anonymous User
    When I am Anonymous User
    And Game with GCode "abc" exists
    And I go to "/pl/controller/abc"
    Then I should be on "/pl/v2.0/dialog/oauth"

  Scenario: Controller is available for User who joined the Game
    Given I am authenticated on "Controller" as "agata"
    And Game with GCode "abc" exists
    When I join Game "abc"
    And I go to "/pl/controller/abc"
    Then I should be on "/pl/controller/abc"
