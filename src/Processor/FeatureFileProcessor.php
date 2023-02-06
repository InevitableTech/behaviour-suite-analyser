<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyserRules\Entities;

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
        $endOfFileIndex = count($contents)-1;
        $tags = '';
        $started = false;

        // Feature declaration block
        $featureDeclarationBlockIndex = ArrayProcessor::getIndexMatching('/^Feature:.*/is', $contents);

        // We could not find a block starting with Feature, lets try to find a closest match and inform the user.
        if ($featureDeclarationBlockIndex === null) {
            $string = '';
            if (is_array($contents) && isset($contents[0]) && strlen($contents[0]) > 0) {
                $nonCompatibleIndex = ArrayProcessor::getIndexMatching('/Feature:.*/is', $contents);
                if ($nonCompatibleIndex !== null) {
                    $string = 'This may be due to encoding issues, closest match: ' . utf8_encode($contents[$nonCompatibleIndex]);
                }
            }

            throw new \Exception('Invalid feature file, no feature declaration found in file. ' . $string);
        }

        foreach ($contents as $index => $line) {
            if ($this->isFeatureDeclaration($line)) {
                $tags = $this->getTagsFromLineBefore($contents, $index);
                $started = true;
            } elseif (
                $started && (
                $this->isBackgroundDeclaration($line) ||
                $this->isScenarioDeclaration($line) ||
                $this->hasTags($line) ||
                $endOfFileIndex === $index)
            ) {
                $feature = array_slice($contents, $featureDeclarationBlockIndex, $index - $featureDeclarationBlockIndex);
                if ($tags) {
                    array_unshift($feature, implode(' ', $tags));
                }
                break;
            }
        }

        return new Entities\Feature(ArrayProcessor::reIndexArray($feature));
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

                $background = ArrayProcessor::reIndexArray(array_slice($contents, $start, $end - $start));

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

    public function getScenarios(array $contents): array {
        $scenarios = [];
        $start = false;
        $startingIndex = 0;
        $endOfFileIndex = count($contents)-1;
        $found = false;

        foreach ($contents as $index => $line) {
            if ($this->isScenarioDeclaration($line)) {
                $found = true;
                // If this is the start of the scenario encountered, don't process it yet, wait until it finishes and another starts.
                if ($start === true) {
                    // Extract scenario content.
                    $scenarioContent = ArrayProcessor::reIndexArray(array_slice($contents, $startingIndex, $index - $startingIndex));

                    // Add scenario tags to scenario.
                    $tags = $this->getTagsFromLineBefore($contents, $startingIndex);

                    // Add tags if they exist to the scenario content.
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

            // If we've made it to the end of the file and a scenario block was found before, assume the
            // rest of the content to be scenario content.
            if ($endOfFileIndex === $index && $found) {
                $scenarioContent= ArrayProcessor::reIndexArray(array_slice($contents, $startingIndex, $index - ($startingIndex-1)));
                $tags = $this->getTagsFromLineBefore($contents, $startingIndex);

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

    private function isFeatureDeclaration(string $line): bool {
        return (bool) preg_match('/^Feature:.*/s', trim($line));
    }

    private function getTagsFromLineBefore(array $contents, $index): array {
        if (! isset($contents[$index - 1])) {
            return [];
        }

        if (preg_match('/^@.*/', trim($contents[$index-1]), $matches)) {
            return $matches;
        }

        return [];
    }
}