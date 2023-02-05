<?php

use PHPUnit\Framework\TestCase;
use Forceedge01\BDDStaticAnalyser\Entities;

final class ScenarioTest extends TestCase
{
    public function testGetStepsSimple()
    {
        $testObject = new Entities\Scenario(5, [
            "  Scenario: This is a test scenario",
            "    Given I login with some details",
            "    When I am on the dashboard",
            "    Then I should see something"
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(3, count($steps));
        self::assertEquals(5, $testObject->lineNumber);
        self::assertEquals([], $testObject->examples);
        self::assertTrue($testObject->active);

        self::assertEquals(6, $steps[0]->lineNumber);
        self::assertEquals('    Given I login with some details', $steps[0]->title);

        self::assertEquals(7, $steps[1]->lineNumber);
        self::assertEquals('    When I am on the dashboard', $steps[1]->title);

        self::assertEquals(8, $steps[2]->lineNumber);
        self::assertEquals('    Then I should see something', $steps[2]->title);
    }

    public function testGetStepsExamples()
    {
        $testObject = new Entities\Scenario(5, [
            "  Scenario: This is a test scenario",
            "    Given I login with <username> and <password>",
            "    When I am on the dashboard",
            "    Then I should see something",
            "    Examples:",
            "      | <username> | <password> |",
            "      | abc | 123!@3%^&* |",
            "      | xyz | 987$%^&*() |",
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(3, count($steps));
        self::assertEquals(5, $testObject->lineNumber);
        self::assertEquals([
            "      | <username> | <password> |",
            "      | abc | 123!@3%^&* |",
            "      | xyz | 987$%^&*() |",
        ], $testObject->examples);
        self::assertTrue($testObject->active);

        self::assertEquals(6, $steps[0]->lineNumber);
        self::assertEquals('    Given I login with <username> and <password>', $steps[0]->title);

        self::assertEquals(7, $steps[1]->lineNumber);
        self::assertEquals('    When I am on the dashboard', $steps[1]->title);

        self::assertEquals(8, $steps[2]->lineNumber);
        self::assertEquals('    Then I should see something', $steps[2]->title);
    }

    public function testGetStepsTable()
    {
        $testObject = new Entities\Scenario(5, [
            "  Scenario: This is a test scenario",
            '    Given I login with "abc" and "123"',
            "    When I am on the dashboard",
            "    Then I should see something:",
            "      | abc |",
            "      | xyz |",
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(3, count($steps));
        self::assertEquals(5, $testObject->lineNumber);
        self::assertEquals([], $testObject->examples);
        self::assertTrue($testObject->active);

        self::assertEquals(6, $steps[0]->lineNumber);
        self::assertEquals('    Given I login with "abc" and "123"', $steps[0]->title);
        self::assertEquals(0, count($steps[0]->table));
        self::assertEquals(0, count($steps[0]->pyString));

        self::assertEquals(7, $steps[1]->lineNumber);
        self::assertEquals('    When I am on the dashboard', $steps[1]->title);
        self::assertEquals(0, count($steps[1]->table));
        self::assertEquals(0, count($steps[1]->pyString));

        self::assertEquals(8, $steps[2]->lineNumber);
        self::assertEquals('    Then I should see something:', $steps[2]->title);
        self::assertIsArray($steps[2]->table);
        self::assertEquals(2, count($steps[2]->table));
        self::assertEquals(0, count($steps[2]->pyString));
    }

    public function testGetStepsFilterOutComments()
    {
        $testObject = new Entities\Scenario(5, [
            "  Scenario: This is a test scenario",
            '    #Given I login with "abc" and "123"',
            "    #When I am on the dashboard",
            "    Then I should see something:",
            "      | abc |",
            "      | xyz |",
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(1, count($steps));
        self::assertEquals(5, $testObject->lineNumber);
        self::assertEquals([], $testObject->examples);
        self::assertTrue($testObject->active);

        self::assertEquals(8, $steps[0]->lineNumber);
        self::assertEquals('    Then I should see something:', $steps[0]->title);
        self::assertEquals(2, count($steps[0]->table));
        self::assertEquals(0, count($steps[0]->pyString));
    }

    public function testGetStepsPyStringSimple()
    {
        $testObject = new Entities\Scenario(15, [
            "  Scenario: This is a test scenario",
            '    Given I login with "abc" and "123"',
            "    When I am on the dashboard:",
            '    """',
            '      A story of great riches',
            '      And it continues',
            '      Examples:',
            '      All above should be ignored.',
            '    """',
            "    Then I should see something",
            "    But I should not see something"
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(4, count($steps));
        self::assertEquals(15, $testObject->lineNumber);
        self::assertEquals([], $testObject->examples);
        self::assertTrue($testObject->active);

        self::assertEquals(16, $steps[0]->lineNumber);
        self::assertEquals('    Given I login with "abc" and "123"', $steps[0]->title);
        self::assertEquals(0, count($steps[0]->table));
        self::assertEquals(0, count($steps[0]->pyString));

        self::assertEquals(17, $steps[1]->lineNumber);
        self::assertEquals('    When I am on the dashboard:', $steps[1]->title);
        self::assertEquals(0, count($steps[1]->table));
        self::assertEquals(4, count($steps[1]->pyString));
        self::assertEquals([
            '      A story of great riches',
            '      And it continues',
            '      Examples:',
            '      All above should be ignored.',
        ], $steps[1]->pyString);

        self::assertEquals(24, $steps[2]->lineNumber);
        self::assertEquals('    Then I should see something', $steps[2]->title);
        self::assertIsArray($steps[2]->table);
        self::assertEquals(0, count($steps[2]->table));
        self::assertEquals(0, count($steps[2]->pyString));

        self::assertEquals(25, $steps[3]->lineNumber);
        self::assertEquals('    But I should not see something', $steps[3]->title);
        self::assertIsArray($steps[3]->table);
        self::assertEquals(0, count($steps[3]->table));
        self::assertEquals(0, count($steps[3]->pyString));
    }

    public function testGetStepsSimpleWithTags()
    {
        $testObject = new Entities\Scenario(5, [
            "  @example @login",
            "  Scenario: This is a test scenario",
            "    Given I login with some details",
            "    When I am on the dashboard",
            "    Then I should see something"
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(3, count($steps));
        self::assertEquals(5, $testObject->lineNumber);
        self::assertEquals([], $testObject->examples);
        self::assertTrue($testObject->active);
        self::assertEquals(['@example', '@login'], $testObject->getTags());

        self::assertEquals(6, $steps[0]->lineNumber);
        self::assertEquals('    Given I login with some details', $steps[0]->title);

        self::assertEquals(7, $steps[1]->lineNumber);
        self::assertEquals('    When I am on the dashboard', $steps[1]->title);

        self::assertEquals(8, $steps[2]->lineNumber);
        self::assertEquals('    Then I should see something', $steps[2]->title);
    }

    public function testGetStepsPyStringAllMixed()
    {
        $testObject = new Entities\Scenario(15, [
            "  @example @login",
            "  Scenario: This is a test scenario",
            '    Given I login with "abc" and "123"',
            "    When I am on the dashboard:",
            '    """',
            '      A story of great riches',
            '      And it continues',
            '      Examples:',
            '      All above should be ignored.',
            '    """',
            "    Then I should see something:",
            "      | abc |",
            "      | xyz |",
            "    But I should not see something",
            "",
            "    Examples:",
            "      | <data> |",
            "      | abc123 |",
            "      | xyz123 |"
        ], true);

        $steps = $testObject->getSteps();

        self::assertEquals(4, count($steps));
        self::assertEquals(15, $testObject->lineNumber);
        self::assertEquals([
            "      | <data> |",
            "      | abc123 |",
            "      | xyz123 |"
        ], $testObject->examples);
        self::assertTrue($testObject->active);

        self::assertEquals(16, $steps[0]->lineNumber);
        self::assertEquals('    Given I login with "abc" and "123"', $steps[0]->title);
        self::assertEquals(0, count($steps[0]->table));
        self::assertEquals(0, count($steps[0]->pyString));

        self::assertEquals(17, $steps[1]->lineNumber);
        self::assertEquals('    When I am on the dashboard:', $steps[1]->title);
        self::assertEquals(0, count($steps[1]->table));
        self::assertEquals(4, count($steps[1]->pyString));
        self::assertEquals([
            '      A story of great riches',
            '      And it continues',
            '      Examples:',
            '      All above should be ignored.',
        ], $steps[1]->pyString);

        self::assertEquals(24, $steps[2]->lineNumber);
        self::assertEquals('    Then I should see something:', $steps[2]->title);
        self::assertIsArray($steps[2]->table);
        self::assertEquals(2, count($steps[2]->table));
        self::assertEquals(0, count($steps[2]->pyString));

        self::assertEquals(27, $steps[3]->lineNumber);
        self::assertEquals('    But I should not see something', $steps[3]->title);
        self::assertIsArray($steps[3]->table);
        self::assertEquals(0, count($steps[3]->table));
        self::assertEquals(0, count($steps[3]->pyString));
    }
}
