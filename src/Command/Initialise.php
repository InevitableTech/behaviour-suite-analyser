<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Forceedge01\BDDStaticAnalyser\Processor;
use Forceedge01\BDDStaticAnalyserRules\Entities;

class Initialise extends Command
{
    public function configure()
    {
        $this->setName('initialise');
        $this->setAliases(['init']);
        $this->setDescription('Initiliase a local config file');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $output->writeln('Initialising new config file');

            if (is_file('.' . DIRECTORY_SEPARATOR . Entities\Config::DEFAULT_NAME)) {
                throw new \Exception('A config file by the name of "' . Entities\Config::DEFAULT_NAME . '" already exists in this folder.');
            }

            if (copy(
                DEFAULT_CONFIG_PATH . Entities\Config::DEFAULT_NAME,
                '.' . DIRECTORY_SEPARATOR . Entities\Config::DEFAULT_NAME
            )) {
                $output->writeln('+ ' . Entities\Config::DEFAULT_NAME);
            } else {
                throw new Exception(
                    'Unable to create config file, likely due to permissions. Copy the following
                    contents into a config.php file manually:' . PHP_EOL . PHP_EOL .
                    file_get_contents(DEFAULT_CONFIG_PATH . Entities\Config::DEFAULT_NAME)
                );
            }
        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return 1;
        }

        return 0;
    }
}
