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

class GenerateUserToken extends BaseCommand
{
    public function __construct()
    {
        $this->client = new Client();
        parent::__construct();
    }

    public function configure()
    {
        $this->setName('token:generate');
        $this->setDescription('Generate and store a user token to authenticate against the web console platform.');
        $this->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to config file', DEFAULT_CONFIG_PATH . Entities\Config::DEFAULT_NAME);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Get necessary user details.
        $output->writeln('<info>Generating a user token will allow you send reports to the web console to track your automation suite results history and track its health.</info>');

        $firstname = $this->ask('Your first name: ', $input, $output);
        $lastname = $this->ask('Your last name: ', $input, $output);
        $email = $this->ask('Your email: ', $input, $output);

        $defaultProjectName = basename(getcwd());
        $projectName = $this->ask('Project name [default]: ', $input, $output, $defaultProjectName);

        $output->writeln('');
        $output->writeln('<info>Registering details for token...</info>');
        $output->writeln('');

        $config = $this->getConfig($input->getOption('config'));
        $console = new Processor\WebConsoleProcessor($config->get('api_key'), new Client());
        $userId = $console->createUser($firstname, $lastname, $email);

        if (! $userId) {
            throw new Exception('Unable to register user.');
        }

        $token = $console->createToken($userId);

        if (! $token) {
            throw new Exception('Unable to generate a token. ');
        }

        $projectId = $console->createProject($token, $projectName, $userId);
        $path = $console->saveTokenDetails($token, $projectId);

        $output->writeln('<comment>Token saved: ' . $path . '</comment>');
    }
}
