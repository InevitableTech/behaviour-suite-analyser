@dev @test-123
Feature: LONG FILE
    In order to test the behaviour analyser
    As the creator of the analyser
    I want to run it against this feature

    Background:
        Given I am able to
        When I do
        Then I should see

    @check
    Scenario: Another Testing something in the feature
        Given I am on the login page
        And I fill in the credentials:
            | abc 1 | abc asdfasdfs2 |
            | abc 1 | abc 2 |
        # And I submit the details "http://kjhakdf"
        Then I should whatever

        Examples:
            | column 1  | column 2   |
            | abc       | askhdf     |
            | kjashdfjk | akdlsfhkja |
            | kjashdfjk | akdlsfhkja |
            | kjashdfjk | akdlsfhkja |

    Scenario: Any comments that are not steps should not be flagged
        # THis is a generic comment which should not be flagged by rule.
        # Given this is flagged.
        And I am here

    @testing-tag
    Scenario: Testing something in the feature
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should be on the dashboard

    Scenario: 3rd Testing something in the feature
        Given I am on the login page
        And I fill in the credentials
        And they submit the details "https://lahsjdhfladh" and "form#username" and "form input.password"
        And they submit the details "whatever"
        And they submit the details "a good set of words.What about this now?"
        And they submit the details "#username"
        And they submit the details "//html/div/abc"
        And they submit the details "//*/div/abc"
        Then he should whatever

    @dev
    Scenario: 4th
        Given I am on the login page
        And I fill in the credentials
        And I submit the details

    @dev
    Scenario: 
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should whatever
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should whatever
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should whatever
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
        Then I should whatever

    Scenario: 4th
        Given I am on the login page
        And I fill in the credentials
        And I submit the details
