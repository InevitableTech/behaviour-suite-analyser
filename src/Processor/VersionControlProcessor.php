<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

class VersionControlProcessor
{
    public static function getRepoUrl(): string
    {
        $output = null;
        $error = null;
        exec('git config --get remote.origin.url 2> /dev/null', $output, $error);

        if ($error === 0 && count($output) == 1) {
            return $output[0];
        }

        return '';
    }

    public static function getBranch(): string
    {
        $output = null;
        $error = null;
        exec('git branch --show-current 2> /dev/null', $output, $error);

        if ($error === 0 && count($output) == 1) {
            return $output[0];
        }

        return '';
    }

    public static function getCommitHash(): string
    {
        $output = null;
        $error = null;
        exec('git rev-parse --verify HEAD 2> /dev/null', $output, $error);

        if ($error === 0 && count($output) == 1) {
            return $output[0];
        }

        return '';
    }
}
