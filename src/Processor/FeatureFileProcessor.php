<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

class FeatureFileProcessor {
    private array $fileObjectLibrary = [];

    public function getFileContent(string $file): Entities\FeatureFileContents {
        if (isset($this->fileObjectLibrary[$file])) {
            return $this->fileObjectLibrary[$file];
        }

        $contents = file($file, FILE_IGNORE_NEW_LINES);

        $feature = $this->getFeature($contents);
        $background = $this->getBackground($contents);
        $scenarios = $this->getScenarios($contents);

        $this->fileObjectLibrary[$file] = new Entities\FeatureFileContents(
            $contents,
            $file,
            $feature,
            $background,
            $scenarios
        );

        return $this->fileObjectLibrary[$file];
    }

    public function getFeature(array $contents): Entities\Feature {
        $feature = [];

        foreach ($contents as $index => $line) {
            if ($this->isBackgroundDeclaration($line) || $this->isScenarioDeclaration($line)) {
                $feature = array_filter(array_slice($contents, 0, $index));
                break;
            }
        }

        return new Entities\Feature(array_values($feature));
    }

    public function getBackground(array $contents): ?Entities\Background {
        $background = [];
        $start = null;
        $end = null;

        foreach ($contents as $index => $line) {
            if ($this->isBackgroundDeclaration($line)) {
                $start = $index;
            }

            if ($this->isScenarioDeclaration($line)) {
                $end = $index;

                // If we've reached a scenario but did not find a background block, then there is none to look for.
                if ($start === null) {
                    return null;
                }

                $background = array_filter(array_slice($contents, $start, $end - $start));

                // Remove tags which belong to the next declaration block.
                $lastStep = end($background);
                if ($this->hasTags($lastStep)) {
                    array_pop($background);
                }

                break;
            }
        }

        return new Entities\Background(
            $start+1,
            $background
        );
    }

    public function getScenarios($contents): array {
        $scenarios = [];
        $start = false;
        $startingIndex = 0;
        $endOfFileIndex = count($contents)-1;

        foreach ($contents as $index => $line) {
            if ($this->isScenarioDeclaration($line)) {
                if ($start === true) {
                    $scenarioContent = array_filter(array_slice($contents, $startingIndex, $index - $startingIndex));

                    // Add scenario tags to scenario.
                    $tags = $this->getTags($contents, $startingIndex);

                    if ($tags) {
                        array_unshift($scenarioContent, implode(' ', $tags));
                    }

                    // Remove any tags that belong to the next scenario from the end of the scenario content.
                    $lastStep = end($scenarioContent);
                    if ($this->hasTags($lastStep)) {
                        array_pop($scenarioContent);
                    }

                    $scenarios[] = new Entities\Scenario(
                        $startingIndex+1,
                        $scenarioContent
                    );

                    $start = false;
                    $startingIndex = 0;
                }

                // Already found?
                if ($start === false) {
                    $start = true;
                    $startingIndex = $index;
                }
            }

            if ($endOfFileIndex === $index) {
                $scenarioContent= array_filter(array_slice($contents, $startingIndex, $index - ($startingIndex-1)));
                $tags = $this->getTags($contents, $startingIndex);

                if ($tags) {
                    array_unshift($scenarioContent, implode(' ', $tags));
                }

                $scenarios[] = new Entities\Scenario(
                    $startingIndex+1,
                    $scenarioContent
                );
                break;
            }
        }

        return $scenarios;
    }

    private function hasTags(string $line): bool {
        return (bool) preg_match('/^@.*/s', trim($line));
    }

    private function isScenarioDeclaration(string $line): bool {
        return (bool) preg_match('/^(Scenario:.*)/s', trim($line));
    }

    private function isBackgroundDeclaration(string $line): bool {
        return (bool) preg_match('/^Background:.*/s', trim($line));
    }

    private function getTags(array $contents, $index): array {
        if (preg_match('/^@.*/', trim($contents[$index-1]), $matches)) {
            return $matches;
        }

        return [];
    }
}