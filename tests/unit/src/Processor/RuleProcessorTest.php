<?php

require __DIR__ . '/../../fixture/FakeRule.php';

use PHPUnit\Framework\TestCase;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use Forceedge01\BDDStaticAnalyser\Processor;

final class RuleProcessorTest extends TestCase
{
    public function setUp(): void
    {
        $this->testObject = new Processor\RulesProcessor([
            'FakeRule'
        ]);
    }

    public function testApplyRulesStringClass()
    {
        $contentObject = $this->createMock(Entities\FeatureFileContents::class);
        $contentObject->feature = $this->createMock(Entities\Feature::class);
        $contentObject->background = '';
        $contentObject->filePath = '/a/file/path';
        $contentObject->scenarios = [];
        $collection = $this->createMock(Entities\OutcomeCollection::class);

        $result = $this->testObject->applyRules($contentObject, $collection);

        self::assertSame($result, $collection);
    }

    public function testApplyRulesStringClassWithScenariosWithNoSteps()
    {
        $scenario = $this->createMock(Entities\Scenario::class);
        $scenario->lineNumber = 49;
        $scenario->expects($this->exactly(2))
            ->method('getSteps')
            ->willReturn([]);

        $contentObject = $this->createMock(Entities\FeatureFileContents::class);
        $contentObject->feature = $this->createMock(Entities\Feature::class);
        $contentObject->background = '';
        $contentObject->filePath = '/a/file/path';
        $contentObject->scenarios = [
            $scenario
        ];
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $collection->expects($this->exactly(1))
            ->method('addSummary')
            ->with($this->isType('string'), $this->isType('string'));

        $result = $this->testObject->applyRules($contentObject, $collection);

        self::assertSame($result, $collection);
    }

    public function testApplyRulesStringClassWithScenariosWithSteps()
    {
        $step1 = $this->createMock(Entities\Step::class);
        $step1->expects($this->exactly(1))
            ->method('getStepDefinition');
        $step2 = $this->createMock(Entities\Step::class);
        $step2->expects($this->exactly(1))
            ->method('getStepDefinition');
        $step3 = $this->createMock(Entities\Step::class);
        $step3->expects($this->exactly(1))
            ->method('getStepDefinition');

        $scenario = $this->createMock(Entities\Scenario::class);
        $scenario->lineNumber = 49;
        $scenario->expects($this->exactly(2))
            ->method('getSteps')
            ->willReturn([
                $step1,
                $step2,
                $step3,
            ]);

        $contentObject = $this->createMock(Entities\FeatureFileContents::class);
        $contentObject->feature = $this->createMock(Entities\Feature::class);
        $contentObject->background = '';
        $contentObject->filePath = '/a/file/path';
        $contentObject->scenarios = [
            $scenario
        ];
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $collection->expects($this->exactly(4))
            ->method('addSummary')
            ->with($this->isType('string'), $this->isType('string'));

        $result = $this->testObject->applyRules($contentObject, $collection);

        self::assertSame($result, $collection);
    }

    public function testApplyRulesStringClassWithScenariosWithStepsAndBackground()
    {
        $step1 = $this->createMock(Entities\Step::class);
        $step1->expects($this->exactly(1))
            ->method('getStepDefinition');
        $step2 = $this->createMock(Entities\Step::class);
        $step2->expects($this->exactly(1))
            ->method('getStepDefinition');
        $step3 = $this->createMock(Entities\Step::class);
        $step3->expects($this->exactly(1))
            ->method('getStepDefinition');

        $scenario = $this->createMock(Entities\Scenario::class);
        $scenario->lineNumber = 123;
        $scenario->expects($this->exactly(2))
            ->method('getSteps')
            ->willReturn([
                $step1,
                $step2,
                $step3,
            ]);

        $contentObject = $this->createMock(Entities\FeatureFileContents::class);
        $contentObject->feature = $this->createMock(Entities\Feature::class);
        $contentObject->filePath = '/a/file/path';
        $contentObject->background = $this->createMock(Entities\Background::class);
        $contentObject->background->lineNumber = 14;
        $contentObject->scenarios = [
            $scenario
        ];
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $collection->expects($this->exactly(5))
            ->method('addSummary')
            ->with($this->isType('string'), $this->isType('string'));

        $result = $this->testObject->applyRules($contentObject, $collection);

        self::assertSame($result, $collection);
    }
}
