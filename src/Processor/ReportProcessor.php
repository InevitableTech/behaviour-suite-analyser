<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;

class ReportProcessor implements ReportProcessorInterface {
    public function __construct(HtmlProcessor $html, FeatureFileProcessor $featureFileProcessor) {
        $this->html = $html;
        $this->featureFileProcessor = $featureFileProcessor;
    }

    public function generate(
        string $path,
        array $severities,
        Entities\OutcomeCollection $collection
    ): string {
        $actualIssuesCount = count($collection->getItems());
        $items = ArrayProcessor::applySeveritiesFilter($collection->getItems(), $severities);
        $issuesToReport = count($items);
        $items = ArrayProcessor::sortByFile($items);
        $items = ArrayProcessor::sortInternalArrayBy($items, 'lineNumber', SORT_ASC);

        $this->html->openTag('html')
            ->openTag('head')
                ->openCloseTag(
                    'style',
                    '.outcome {display:block; width: 100%; float: left;} div.row {padding: 10px; width: 99%; float: left;} .error { background: pink; border: 1px solid red;} .success {background: yellowgreen; border: 1px solid green; } .outcome {border: 1px dashed grey; margin-bottom: 5px;} .file {margin-top: 15px;} .right {float: right; display: inline-block;}'
                )
                ->openCloseTag(
                    'script',
                    'function resolve(id) {
                        document.querySelectorAll("#"+id+" .violation")[0].classList.remove("error");
                        document.querySelectorAll("#"+id+" .violation")[0].classList.add("success");
                    }

                    function unresolve(id) {
                        document.querySelectorAll("#"+id+" .violation")[0].classList.remove("success");
                        document.querySelectorAll("#"+id+" .violation")[0].classList.add("error");
                    }'
                )
            ->closeTag('head')
            ->openTag('body')
                ->openCloseTag('h1', 'BDD Analysis Report')
                ->openCloseTag('div', "Total $actualIssuesCount violations, displaying filtered $issuesToReport");

        foreach ($items as $file => $outcomes) {
            $this->html->openCloseTag('div,b', $file . ' (Violations: '. count($outcomes) .')', 'row file');

            foreach ($outcomes as $index => $outcome) {
                $id = 'outcome-' . str_replace(['/', '.'], '', $file) . $index;
                $this->html->openTag("div id='{$id}'", 'outcome');
                $this->generateSingleOutcomeSummary($outcome, $this->html, $id);
                $this->html->closeTag('div');
            }
        }

        $this->html->closeTag('body')->closeTag('html');
        $this->saveFile($path, $this->html->generate());

        return $path;
    }

    private function generateSingleOutcomeSummary(Entities\Outcome $outcome, HtmlProcessor $html, $id) {
        $html->openTag('div', 'row')
            ->openCloseTag("div,button onclick='resolve(\"${id}\")'", 'Resolved', 'right')
            ->openCloseTag("div,button onclick='unresolve(\"${id}\")'", 'Unresolved', 'right')
            ->openCloseTag('a href="file://' . $outcome->file . '"', 'Line ' . $outcome->lineNumber . ': ' . $outcome->cleanStep . ' ('. $outcome->getRuleShortName() .')')
        ->closeTag('div');

        if ($outcome->scenario) {
            $html->openCloseTag('div', 'Scenario: ' . $outcome->scenario, 'row');
        }

        if ($outcome->violatingLine) {
            $html->openCloseTag('div', 'Violating line: ' . $outcome->violatingLine, 'row');
        }

        $html->openCloseTag('div', 'Violation: ' . $outcome->message . ' (' . $outcome->severity . ')', 'row error violation');
    }

    private function saveFile(string $path, string $content) {
        $folderPath = dirname($path);
        if (!is_dir($folderPath) && !mkdir($folderPath, 0777, true)) {
            throw new \Exception('Unable to create folder for report at path ' . $folderPath);
        }
        file_put_contents($path, $content);
    }
}