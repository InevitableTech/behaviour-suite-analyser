<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Forceedge01\BDDStaticAnalyser\Processor;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use Symfony\Component\Yaml\Yaml;
use GuzzleHttp\Client;

class Scan extends Command
{
    public function configure()
    {
        $this->setName('scan');
        $this->setAliases(['lint']);
        $this->setDescription('Analyse BDD script files and find violations based on rules enabled in the config file');
        $this->addArgument('directory', InputArgument::REQUIRED, 'Directory to scan');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to config file', DEFAULT_CONFIG_PATH . Entities\Config::DEFAULT_NAME);
        $this->addOption('severities', 's', InputOption::VALUE_REQUIRED, 'Severities to display', '0,1,2,3,4');
        $this->addOption('rules', 'r', InputOption::VALUE_NONE, 'Display rules applied');
        $this->addOption('project-token', 'p', InputOption::VALUE_REQUIRED, 'Run an analysis against the web console using a project token (skips registering token (CI)');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $path = $input->getArgument('directory');
            $severities = $input->getOption('severities');
            $configFile = $input->getOption('config');
            $displayRules = $input->getOption('rules');

            $configPath = Entities\Config::getValidConfigPath($configFile);
            $config = new Entities\Config($configPath, Yaml::parseFile($configPath));

            if ($displayRules) {
                $output->writeln('Active rules: ' . print_r($config->get('rules'), true));
                return 0;
            }

            $displayProcessorClass = $config->get('display_processor');
            $displayProcessor = new $displayProcessorClass();
            $displayProcessor->setOutput($output);
            $dirToScan = realpath($path);
            $displayProcessor->inputSummary($path, $severities, $config->path, $dirToScan);

            if (! $path) {
                throw new Exception('A path must be provided (-d=<path>) to scan for files.');
            }

            if (! $dirToScan || ! is_dir($dirToScan)) {
                throw new Exception("Path: '$path' must point to the folder where feature files are stored.");
            }

            $severities = explode(',', $severities);
            $featureFileProcessor = new Processor\FeatureFileProcessor();
            $rulesProcessor = new Processor\RulesProcessor($config->get('rules'));
            $files = Processor\DirectoryProcessor::getAllFeatureFiles($dirToScan, $config->get('feature_file_extension'));
            $outcomeCollection = new Entities\OutcomeCollection();

            if (! $files) {
                throw new Exception("No feature files found in path '$dirToScan'");
            }

            $output->writeln(print_r($files, true), OutputInterface::VERBOSITY_DEBUG);

            foreach ($files as $file) {
                try {
                    $fileContents = $featureFileProcessor->getFileContent($file);
                } catch (Exception $e) {
                    self::error($output, sprintf('Unable to process feature file contents in file "%s".', $file), $e);
                    continue;
                }

                $output->writeln(print_r($fileContents, true), OutputInterface::VERBOSITY_DEBUG);

                try {
                    $outcomeCollection->summary['linesCount'][$file] = count($fileContents->raw);
                    $rulesProcessor->applyRules($fileContents, $outcomeCollection);
                } catch (Exception $e) {
                    self::error($output, sprintf(''), $e);
                    continue;
                }
            }

            Processor\SummaryProcessor::setSummary($outcomeCollection);

            $output->writeln(print_r($outcomeCollection, true), OutputInterface::VERBOSITY_DEBUG);

            $displayProcessor->displayOutcomes($outcomeCollection, $severities);
            $reportPath = $this->generateReports($config, $severities, $outcomeCollection, $featureFileProcessor);
            $displayProcessor->printSummary($outcomeCollection, $reportPath);

            // Send the report off.
            if ($config->get('web_console_report')) {
                $webConsole = new Processor\WebConsoleProcessor(
                    $config->get('api_key'),
                    new Client(),
                    $input->getOption('project-token')
                );
                $analysisId = $webConsole->sendAnalysis(
                    clone $outcomeCollection,
                    $severities
                );

                $webConsole->printConsoleLink($output, $webConsole->getCred('project_id'), $analysisId);
            }

            if ($outcomeCollection->getCount() > 0) {
                return 1;
            }
        } catch (Exception $e) {
            self::error($output, $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function generateReports(
        Entities\Config $config,
        array $severities,
        Entities\OutcomeCollection $outcomeCollection,
        Processor\FeatureFileProcessor $featureFileProcessor
    ): string {
        $reportPath = '';

        if ($config->get('enable_html_report')) {
            $reportProcessorClass = $config->get('report_processor');
            $htmlReportProcessor = new $reportProcessorClass(new Processor\HtmlProcessor(), $featureFileProcessor);
            $reportPath = $htmlReportProcessor->generate(
                $config->getPath('html_report_path'),
                $severities,
                clone $outcomeCollection
            );
        }

        if ($config->get('enable_json_report')) {
            $reportProcessorClass = $config->get('json_report_processor');
            $jsonReportProcessor = new $reportProcessorClass();
            $jsonReportProcessor->generate(
                $config->getPath('json_report_path'),
                $severities,
                clone $outcomeCollection
            );
        }

        return $reportPath;
    }

    private static function error(OutputInterface $output, string $message, \Exception $e = null)
    {
        $output->write('<error>==> Error: ' . $message);

        if ($e !== null) {
            $output->write(' Exception: ' . $e->getMessage());
        }

        $output->writeln('</error>' . PHP_EOL);
    }
}
