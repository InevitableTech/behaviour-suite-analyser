<?php

namespace Forceedge01\BDDStaticAnalyser\Entities;

class FeatureFileContents {
    public function __construct(
        array $raw,
        string $filePath,
        array $feature,
        ?Background $background,
        array $scenarios
    ) {
        $this->raw = $raw;
        $this->filePath = $filePath;
        $this->feature = $feature;
        $this->background = $background;
        $this->scenarios = $scenarios;
    }
}
