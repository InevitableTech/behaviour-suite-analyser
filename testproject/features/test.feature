Feature: testing
    In order to test the behaviour analyser
    As the creator of the analyser
    I want to run it against this feature

    Background:
        Given I am able to
        When I do
        Then I should see

    Scenario: Testing something in the feature
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should be on the dashboard

    Scenario: Another Testing something in the feature
        Given I am on the login page
        And I fill in the credentials
        And I submit the details "http://kjhakdf"
        Then I should whatever

        Examples:
            | column 1  | column 2   |
            | abc       | askhdf     |
            | kjashdfjk | akdlsfhkja |

    Scenario: 3rd Testing something in the feature
        Given I am on the login page
        And I fill in the credentials
        And I submit the details "https://lahsjdhfladh"
        Then I should whatever

    Scenario: 4th
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should whatever
