Feature: Player can see a summary showing what was his score. He can share this summary with friends.

  @BC
  Scenario: Summary page displays score when only result is provided without an info about possible max score.
    This is for backward compatibility.
    When I go to "/pl/summary/?username=c1&result=11&tagName=%23dzien_dziecka&tagId=46&checksum=Mjc%3D"
    And I should see "Gratulacje"
    And I should see "c1"
    And I should see "#dzien_dziecka to 11 punktów"
    And I should see "55%"

  Scenario: Summary page displays score and percent of max score.
    When I go to "/pl/summary/?username=c1&result=11&playerCount=4&tagName=%23dzien_dziecka&tagId=46&checksum=Mjc%3D"
    And I should see "Gratulacje"
    And I should see "c1"
    And I should see "#dzien_dziecka to 11 punktów"
    And I should see "w grze brało udział 4 graczy"
    And I should see "14%"