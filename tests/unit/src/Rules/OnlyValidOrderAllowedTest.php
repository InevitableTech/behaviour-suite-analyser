<?php

use PHPUnit\Framework\TestCase;
use Forceedge01\BDDStaticAnalyser\Entities;
use Forceedge01\BDDStaticAnalyser\Rules;

final class OnlyValidOrderAllowedTest extends TestCase
{
    public function setUp(): void
    {
        $featureFileContents = $this->createStub(Entities\FeatureFileContents::class);
        $featureFileContents->filePath = './abc.feature';

        $this->testObject = new Rules\OnlyValidOrderAllowed();
        $this->testObject->setFeatureFileContents($featureFileContents);
    }

    public function testApplyOnScenarioNoSteps()
    {
        $collection = $this->createMock(Entities\OutcomeCollection::class);

        $scenario = $this->createStub(Entities\Scenario::class);

        $scenario->method('getActiveSteps')
            ->willReturn([]);

        $collection->expects($this->never())
            ->method('addOutcome');

        $this->testObject->applyOnScenario(
            $scenario,
            $collection
        );
    }

    public function testApplyOnScenarioAllGood()
    {
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $scenario = $this->createStub(Entities\Scenario::class);

        $step = $this->createStub(Entities\Step::class);
        $step->lineNumber = 50;
        $step->title = '   Given I am doing this test';
        $step->trimmedTitle = 'Given I am doing this test';
        $step->method('getKeyword')->willReturn('given');

        $step2 = $this->createStub(Entities\Step::class);
        $step2->lineNumber = 52;
        $step2->title = '   When I do something';
        $step2->trimmedTitle = trim($step2->title);
        $step2->method('getKeyword')->willReturn('when');

        $step3 = $this->createStub(Entities\Step::class);
        $step3->lineNumber = 53;
        $step3->title = '   And I do something';
        $step3->trimmedTitle = trim($step3->title);
        $step3->method('getKeyword')->willReturn('and');

        $step4 = $this->createStub(Entities\Step::class);
        $step4->lineNumber = 54;
        $step4->title = '   Then I do something';
        $step4->trimmedTitle = trim($step4->title);
        $step4->method('getKeyword')->willReturn('then');

        $step5 = $this->createStub(Entities\Step::class);
        $step5->lineNumber = 55;
        $step5->title = '   But I do something';
        $step5->trimmedTitle = trim($step5->title);
        $step5->method('getKeyword')->willReturn('but');

        $scenario->method('getActiveSteps')
            ->willReturn([
                $step,
                $step2,
                $step3,
                $step4,
                $step5
            ]);

        $collection->expects($this->never())
            ->method('addOutcome');

        $this->testObject->applyOnScenario(
            $scenario,
            $collection
        );
    }

    public function testApplyOnScenarioAllGoodStartWithWhen()
    {
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $scenario = $this->createStub(Entities\Scenario::class);

        $step2 = $this->createStub(Entities\Step::class);
        $step2->lineNumber = 52;
        $step2->title = '   When I do something';
        $step2->trimmedTitle = trim($step2->title);
        $step2->method('getKeyword')->willReturn('when');

        $step3 = $this->createStub(Entities\Step::class);
        $step3->lineNumber = 53;
        $step3->title = '   And I do something';
        $step3->trimmedTitle = trim($step3->title);
        $step3->method('getKeyword')->willReturn('and');

        $step4 = $this->createStub(Entities\Step::class);
        $step4->lineNumber = 54;
        $step4->title = '   Then I do something';
        $step4->trimmedTitle = trim($step4->title);
        $step4->method('getKeyword')->willReturn('then');

        $step5 = $this->createStub(Entities\Step::class);
        $step5->lineNumber = 55;
        $step5->title = '   But I do something';
        $step5->trimmedTitle = trim($step5->title);
        $step5->method('getKeyword')->willReturn('but');

        $scenario->method('getActiveSteps')
            ->willReturn([
                $step2,
                $step3,
                $step4,
                $step5
            ]);

        $collection->expects($this->never())
            ->method('addOutcome');

        $this->testObject->applyOnScenario(
            $scenario,
            $collection
        );
    }

    public function testApplyOnScenario()
    {
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $scenario = $this->createStub(Entities\Scenario::class);

        $step = $this->createStub(Entities\Step::class);
        $step->lineNumber = 50;
        $step->title = '   Given I am doing this test';
        $step->trimmedTitle = 'Given I am doing this test';
        $step->method('getKeyword')->willReturn('given');

        $step2 = $this->createStub(Entities\Step::class);
        $step2->lineNumber = 51;
        $step2->title = '   Then I do something';
        $step2->trimmedTitle = 'Then I do something';
        $step2->method('getKeyword')->willReturn('then');

        $scenario->method('getActiveSteps')
            ->willReturn([
                $step,
                $step2
            ]);

        $collection->expects($this->once())->method('addOutcome')
            ->with($this->callback(function ($outcome) {
                self::assertEquals('Expected step to start with keyword "when", got "then" instead. Are you missing a "when" step?', $outcome->message);

                return true;
            }));

        $this->testObject->applyOnScenario(
            $scenario,
            $collection
        );
    }

    public function testApplyOnScenarioDifferent()
    {
        $collection = $this->createMock(Entities\OutcomeCollection::class);
        $scenario = $this->createStub(Entities\Scenario::class);

        $step = $this->createStub(Entities\Step::class);
        $step->lineNumber = 50;
        $step->title = '   And I am doing this test';
        $step->trimmedTitle = trim($step->title);
        $step->method('getKeyword')->willReturn('and');

        $step2 = $this->createStub(Entities\Step::class);
        $step2->lineNumber = 51;
        $step2->title = '   Then I do something';
        $step2->trimmedTitle = trim($step2->title);
        $step2->method('getKeyword')->willReturn('then');

        $scenario->method('getActiveSteps')
            ->willReturn([
                $step,
                $step2
            ]);

        $collection->expects($this->once())->method('addOutcome')
            ->with($this->callback(function ($outcome) {
                self::assertEquals('Expected step to start with keyword "given", got "and" instead. Are you missing a "given" step?', $outcome->message);

                return true;
            }));

        $this->testObject->applyOnScenario(
            $scenario,
            $collection
        );
    }
}
