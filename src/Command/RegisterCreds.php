<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Command;

use Exception;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use Forceedge01\BDDStaticAnalyser\Processor;
use GuzzleHttp\Client;

class RegisterCreds extends BaseCommand
{
    public function __construct()
    {
        $this->client = new Client();
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('token:register');
        $this->setDescription('Generate and store a user token to authenticate against the web console platform.');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to config file', DEFAULT_CONFIG_PATH . Entities\Config::DEFAULT_NAME);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Get necessary user details.
        $output->writeln('<info>Generating a user token will allow you send reports to the web console to track your automation suite results history and track its health.</info>');

        $token = $this->ask('Enter user token: ', $input, $output, null, true);

        if (! $token) {
            throw new Exception('Unable to generate a token.');
        }

        $defaultProjectName = basename(getcwd());
        $projectName = $this->ask('Project name [default]: ', $input, $output, $defaultProjectName);

        $output->writeln('');
        $output->writeln('<info>Creating project...</info>');
        $output->writeln('');

        $config = $this->getConfig($input->getOption('config'));
        $console = new Processor\WebConsoleProcessor($config->get('api_key'), new Client());
        $console->setToken($token);

        $userId = $console->getUserId();
        $projectId = $console->createProject($projectName, $userId);

        $path = $console->saveCreds([
            'user_token' => $token,
            'project_id' => $projectId,
            'user_id' => $userId
        ]);

        $output->writeln('<comment>Token saved: ' . $path . '</comment>');
    }
}
