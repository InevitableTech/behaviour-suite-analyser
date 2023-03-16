<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Command;

use Symfony\Component\Console\Command\Command;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Question\Question;

/**
 * BaseCommand
 */
class BaseCommand extends Command
{
    public function getConfig(string $configFile): Entities\Config
    {
        $configPath = Entities\Config::getValidConfigPath($configFile);

        return new Entities\Config($configPath, Yaml::parseFile($configPath));
    }

    protected function ask(string $question, $input, $output, string $default = null, $hidden = false): string
    {
        $question = new Question(str_replace('[default]', '[' . $default . ']', $question), $default);
        $question->setHidden($hidden);

        $answer = $this->getHelper('question')->ask($input, $output, $question);

        return $answer;
    }
}
