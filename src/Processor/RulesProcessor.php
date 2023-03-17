<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Rules;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use UnexpectedValueException;

class RulesProcessor
{
    private $rules = [];
    private $ruleObjects = [];
    private $outcome = [];

    public function __construct(array $rules)
    {
        $this->rules = $rules;
    }

    /**
     * All rules are applied everytime on each file.
     */
    public function applyRules(
        Entities\FeatureFileContents $contentObject,
        Entities\OutcomeCollection $collection
    ): Entities\OutcomeCollection {
        $collection->summary['files']++;
        $collection->summary['activeRules'] = $this->rules;

        foreach ($this->rules as $ruleDetails) {
            try {
                $ruleClass = $this->getRuleClass($ruleDetails);
                $ruleArgs = $this->getRuleArgs($ruleDetails);

                $rule = $this->getRule($ruleClass, $ruleArgs);
                $rule->beforeApply($contentObject->filePath, $collection);
                $this->outcome[] = $this->applyRule($contentObject, $rule, $collection);
            } catch (\Exception $e) {
                throw new \Exception(sprintf(
                    'Unable to apply rule "%s" on file "%s". Error: %s',
                    $ruleClass,
                    $contentObject->filePath,
                    $e->getMessage()
                ));
                continue;
            }
        }

        return $collection;
    }

    private function getRuleClass($ruleDetails): string
    {
        return is_array($ruleDetails) ? key($ruleDetails) : $ruleDetails;
    }

    private function getRuleArgs($ruleDetails): array
    {
        if (is_string($ruleDetails)) {
            return [];
        }

        $key = key($ruleDetails);

        if (!isset($ruleDetails[$key]['args'])) {
            throw new UnexpectedValueException('Expected to receive args in array format.');
        }

        return $ruleDetails[$key]['args'];
    }

    public function getRule(string $rule, array $params = null): Rules\RuleInterface
    {
        if (isset($this->ruleObjects[$rule])) {
            return $this->ruleObjects[$rule]->reset();
        }

        $this->ruleObjects[$rule] = new $rule($params);

        return $this->ruleObjects[$rule];
    }

    public function applyRule(
        Entities\FeatureFileContents $contentObject,
        Rules\RuleInterface $rule,
        Entities\OutcomeCollection $collection
    ): Entities\OutcomeCollection {
        $rule->setFeatureFileContents($contentObject);
        $rule->applyOnFeature($contentObject, $collection);

        foreach ($contentObject->feature->getTags() as $tag) {
            $collection->addSummary('tags', $tag);
        }

        if ($contentObject->background) {
            $collection->addSummary('backgrounds', $contentObject->filePath . $contentObject->background->lineNumber);
            $rule->applyOnBackground($contentObject->background, $collection);
        }

        foreach ($contentObject->scenarios as $scenario) {
            foreach ($scenario->getTags() as $tag) {
                $collection->addSummary('tags', $tag);
            }

            $collection->addSummary('scenarios', $contentObject->filePath . $scenario->lineNumber);
            $rule->setScenario($scenario);
            $rule->beforeApplyOnScenario($scenario, $collection);
            $rule->applyOnScenario($scenario, $collection);

            // Steps
            $steps = $scenario->getSteps();

            foreach ($steps as $index => $step) {
                $collection->addSummary('activeSteps', $step->getStepDefinition());
                $rule->beforeApplyOnStep($step, $collection);
                $rule->applyOnStep($step, $collection);
                $rule->afterApplyOnStep($step, $collection);
            }

            $rule->afterApplyOnScenario($scenario, $collection);
        }

        $rule->applyAfterFeature($contentObject, $collection);

        return $collection;
    }
}
