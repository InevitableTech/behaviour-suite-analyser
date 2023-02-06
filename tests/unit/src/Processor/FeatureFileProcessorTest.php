<?php

use PHPUnit\Framework\TestCase;
use Forceedge01\BDDStaticAnalyser\Entities;
use Forceedge01\BDDStaticAnalyser\Processor;

final class FeatureFileProcessorTest extends TestCase
{
    public function setUp(): void
    {
        $this->testObject = new Processor\FeatureFileProcessor();
    }

    public function testGetScenariosSingle()
    {
        $contents = [
            "Feature: abc",
            "  Scenario: first 1",
            "    Given I am",
            "    When I do",
            "    Then I see",
        ];

        $scenarios = $this->testObject->getScenarios($contents);

        self::assertEquals(1, count($scenarios));
        self::assertEquals(Entities\Scenario::class, get_class($scenarios[0]));
        self::assertEquals(2, $scenarios[0]->lineNumber);
        self::assertEquals([
            "  Scenario: first 1",
            "    Given I am",
            "    When I do",
            "    Then I see"
        ], $scenarios[0]->scenario);
    }

    public function testGetScenariosMultiple()
    {
        $contents = [
            "Feature: abc",
            "  Scenario: first 1",
            "    Given I am",
            "    When I do",
            "    Then I see",
            "",
            "  @negate",
            "  Scenario: second",
            "    Given I am",
            "    When I do",
            "    Then I see",
            "    But I dont see"
        ];

        $scenarios = $this->testObject->getScenarios($contents);

        self::assertEquals(2, count($scenarios));
        self::assertEquals(Entities\Scenario::class, get_class($scenarios[0]));
        self::assertEquals(2, $scenarios[0]->lineNumber);
        self::assertEquals([
            "  Scenario: first 1",
            "    Given I am",
            "    When I do",
            "    Then I see",
            ""
        ], $scenarios[0]->scenario);

        self::assertEquals(8, $scenarios[1]->lineNumber);
        self::assertEquals([
            "@negate",
            "  Scenario: second",
            "    Given I am",
            "    When I do",
            "    Then I see",
            "    But I dont see"
        ], $scenarios[1]->scenario);
    }

    public function testGetScenariosMultipleComplex()
    {
        $contents = [
            "Feature: abc",
            "  Scenario: first 1",
            "    Given I am",
            "    When I do",
            "    Then I see:",
            '    """',
            "      Some pystring",
            '    """',
            "",
            "  @negate",
            "  Scenario: second",
            "    Given I am",
            "    When I do",
            "    Then I see:",
            "      | abc | 123 |",
            "    But I dont see"
        ];

        $scenarios = $this->testObject->getScenarios($contents);

        self::assertEquals(2, count($scenarios));
        self::assertEquals(Entities\Scenario::class, get_class($scenarios[0]));
        self::assertEquals(2, $scenarios[0]->lineNumber);
        self::assertEquals([
            "  Scenario: first 1",
            "    Given I am",
            "    When I do",
            "    Then I see:",
            '    """',
            "      Some pystring",
            '    """',
            "",
        ], $scenarios[0]->scenario);

        self::assertEquals(11, $scenarios[1]->lineNumber);
        self::assertEquals([
            "@negate",
            "  Scenario: second",
            "    Given I am",
            "    When I do",
            "    Then I see:",
            "      | abc | 123 |",
            "    But I dont see"
        ], $scenarios[1]->scenario);
    }
}
