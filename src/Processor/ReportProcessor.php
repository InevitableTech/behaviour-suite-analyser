<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;

class ReportProcessor implements ReportProcessorInterface
{
    public function __construct(HtmlProcessor $html, FeatureFileProcessor $featureFileProcessor)
    {
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
        $ruleItems = ArrayProcessor::sortByRule($items);
        $items = ArrayProcessor::sortByFile($items);
        $filesCount = count($items);
        $items = ArrayProcessor::sortInternalArrayBy($items, 'lineNumber', SORT_ASC);
        $severitiesString = implode(', ', $severities);

        $this->html->openTag('html')
            ->openTag('head')
                ->openCloseTag(
                    'style',
                    '
                    .outcome {display:block; width: 100%; float: left;}
                    div.row {padding: 10px; width: 99%; float: left;}
                    .error { background: pink; border: 1px solid red;}
                    .success {background: yellowgreen; border: 1px solid green; }
                    .outcome {border: 1px dashed grey; margin-bottom: 5px;}
                    .file {margin-top: 15px;}
                    .right {float: right; display: inline-block;}
                    .charts {float: left; padding: 10px 0px; margin: 10px 0px; width: 100%;}
                    '
                )
                ->openTag('script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"')
                ->closeTag('script')
                ->openCloseTag(
                    'script',
                    '
                    function resolve(id) {
                        document.querySelectorAll("#"+id+" .violation")[0].classList.remove("error");
                        document.querySelectorAll("#"+id+" .violation")[0].classList.add("success");
                    }

                    function unresolve(id) {
                        document.querySelectorAll("#"+id+" .violation")[0].classList.remove("success");
                        document.querySelectorAll("#"+id+" .violation")[0].classList.add("error");
                    }
                    '
                )
            ->closeTag('head')
            ->openTag('body')
                ->openCloseTag('h1', 'BDD Analysis Report')
                ->tag('hr')
                ->openCloseTag('div', "Files: $filesCount. Total $actualIssuesCount violations, displaying filtered $issuesToReport (Severities: $severitiesString).")
                ->tag('hr')
                ->openTag('div', 'charts')
                    ->openTag('div style="width: 400px; float: left"')
                        ->openCloseTag('canvas id="feature-split"', '')
                    ->closeTag('div')
                    ->openTag('div style="width: 400px; float: left"')
                        ->openCloseTag('canvas id="rule-split"', '')
                    ->closeTag('div')
                    ->openTag('div style="width: 900px; float: left"')
                        ->openCloseTag('canvas id="rule-outcomes-split"', '')
                    ->closeTag('div')
                ->closeTag('div')
                ->tag('hr');

        foreach ($items as $file => $outcomes) {
            $this->html->openCloseTag('div,b', $file . ' (Violations: '. count($outcomes) .')', 'row file')->tag('hr');

            foreach ($outcomes as $index => $outcome) {
                $id = 'outcome-' . str_replace(['/', '.'], '', $file) . $index;
                $this->html->openTag("div id='{$id}'", 'outcome');
                $this->generateSingleOutcomeSummary($outcome, $this->html, $id);
                $this->html->closeTag('div');
            }
        }

        // Draw charts
        // pie: total scenarios vs ones that have errors
        // pie: total steps vs ones that have errors
        $this->html
            ->openCloseTag(
                'script type="text/javascript"',
                '
                document.addEventListener("DOMContentLoaded", function() {
                    const ctx = document.getElementById("feature-split");
                    const ctx2 = document.getElementById("rule-split");
                    const ctx3 = document.getElementById("rule-outcomes-split");

                    new Chart(ctx, ' . json_encode($this->getChartData('doughnut', 'Features split', [
                        'Features with no errors' => $collection->getSummary('files') - count($items),
                        'Features with errors' => count($items)
                    ])) . ');

                    new Chart(ctx2, ' . json_encode($this->getChartData('doughnut', 'Rule Split', [
                        'Rules with no errors' => $collection->getSummaryCount('activeRules') - count($ruleItems),
                        'Rules caught errors' => count($ruleItems)
                    ])) . ');

                    new Chart(ctx3, ' . json_encode($this->getChartData(
                        'bar',
                        'Rule outcome split',
                        $this->getRuleCounts($ruleItems)
                    )) . ');
                }, false);
                '
            )
            ->closeTag('body')
            ->closeTag('html');
        $this->saveFile($path, $this->html->generate());

        return $path;
    }

    private function getRuleCounts(array $ruleItems): array
    {
        $data = [];
        foreach ($ruleItems as $rule => $outcomes) {
            $data[$rule] = count($outcomes);
        }

        return $data;
    }

    /**
     * pie: total features vs ones that have errors
     */
    private function getChartData(string $type, string $label, array $dataset)
    {
        $skeleton = [
            'type' => $type,
            'data' => [
                'labels' => [],
                'datasets' => [
                    [
                        'label' => $label,
                        'data' => [],
                        'backgroundColor' => [
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                            $this->getBackgroundColors(),
                        ]
                    ]
                ]
            ]
        ];

        foreach ($dataset as $label => $data) {
            $skeleton['data']['labels'][] = $label;
            $skeleton['data']['datasets'][0]['data'][] = $data;
        }

        return $skeleton;
    }

    private function getBackgroundColors(): string
    {
        $color1 = rand(0, 255);
        $color2 = rand(0, 255);
        $color3 = rand(0, 255);

        return "rgb($color1, $color2, $color3)";
    }

    private function generateSingleOutcomeSummary(Entities\Outcome $outcome, HtmlProcessor $html, $id)
    {
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

    private function saveFile(string $path, string $content)
    {
        $folderPath = dirname($path);
        if (!is_dir($folderPath) && !mkdir($folderPath, 0777, true)) {
            throw new \Exception('Unable to create folder for report at path ' . $folderPath);
        }
        file_put_contents($path, $content);
    }
}
