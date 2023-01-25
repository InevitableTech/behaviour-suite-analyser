<?php

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Forceedge01\BDDStaticAnalyser\Entities;

interface ReportProcessorInterface {
    public function generate(
        string $path,
        array $severities,
        Entities\OutcomeCollection $collection
    ): string;
}