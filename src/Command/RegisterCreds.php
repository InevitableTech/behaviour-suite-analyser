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
        $this->addOption('project-token', 'p', InputOption::VALUE_REQUIRED, 'Run an analysis against the web console using a project token (skips registering token (CI)');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Set api_key before calling this or throw exception.

        // Get necessary user details.
        $output->writeln('<info>Generating a user token will allow you send reports to the web console to track your automation suite results history and track its health.</info>');

        $config = $this->getConfig($input->getOption('config'));
        $console = new Processor\WebConsoleProcessor($config->get('api_key'), new Client());

        $projectToken = $input->getOption('project-token');
        $path = null;

        if ($projectToken) {
            $path = $console->saveToken($projectToken);
        } else {
            $token = $this->ask('Enter user token: ', $input, $output, null, true);

            if (! $token) {
                throw new Exception('You must provide a token, generate a token from the remote bdd analyser web console.');
            }

            $console->setToken($token);

            // Get existing projects associated with user.
            $projects = $console->getUserProjects();

            $ids = array_column($projects, 'id');
            $projectNames = array_column($projects, 'name');
            print_r($projectNames);

            $createNewOption = 'Create new';
            $projectIndex = $this->ask(
                'Select which project number you would like to use [%s]',
                $input,
                $output,
                $createNewOption
            );

            $userId = $console->getUserId();

            $projectId = null;
            if ($projectIndex == $createNewOption) {
                $defaultProjectName = basename(getcwd());
                $projectName = $this->ask('Project name [%s]: ', $input, $output, $defaultProjectName);

                $defaultRepoUrl = Processor\VersionControlProcessor::getRepoUrl();
                $repoUrl = $this->ask('Repository url [%s]: ', $input, $output, $defaultRepoUrl);

                $defaultBranch = 'main';
                $branch = $this->ask('Default branch [%s]: ', $input, $output, $defaultBranch);

                $output->writeln('');
                $output->writeln('<info>Creating project...</info>');
                $output->writeln('');

                $projectId = $console->createProject($projectName, $repoUrl, $branch, $userId);
            } else {
                $projectId = $ids[$projectIndex];
            }

            $path = $console->saveCreds([
                'user_token' => $token,
                'project_id' => $projectId,
                'user_id' => $userId
            ]);
        }

        $output->writeln('<comment>Token saved: ' . $path . '</comment>');
    }
}
