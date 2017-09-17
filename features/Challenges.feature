Feature: Challenges are sets of questions creating an interesting issue

  @Challenges
  Scenario: Challenges list shows only challenges for requested locale
    When I am Anonymous User
    And I go to "/pl/challenges-list"
    Then I should see "wyzwanie_1" in the "body *" element
    And I should not see "challenge_1" in the "body *" element

  @Challenges
  Scenario: Challenges list shows all categories in all locales
    When I am Anonymous User
    And I go to "/pl/challenges-list"
    Then I should see "Opis matematyki" in the "body *" element

  @Challenges
  Scenario: Challenges list shows all categories in all locales
    When I am Anonymous User
    And I go to "/en/challenges-list"
    Then I should see "Math description" in the "body *" element