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
        $this->addOption('token', 't', InputOption::VALUE_REQUIRED, 'Register a token from the web console');
        $this->addOption('project-id', 'p', InputOption::VALUE_REQUIRED, 'Register token for this project id');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        // Get necessary user details.
        $output->writeln('<info>Generating a user token will allow you send reports to the web console to track your automation suite results history and track its health.</info>');

        $config = $this->getConfig($input->getOption('config'));
        $console = new Processor\WebConsoleProcessor($config->get('api_key', ''), new Client());

        $userToken = $input->getOption('token');
        $projectId = (int) $input->getOption('project-id');
        $path = null;

        if (! $userToken) {
            $userToken = $this->ask('Enter user token: ', $input, $output, null, true);

            if (! $userToken) {
                throw new Exception('You must provide a token, generate a token from the remote bdd analyser web console.');
            }
        }

        $console->setToken($userToken);
        $userId = $console->getUserId();

        // Get existing projects associated with user.
        if ($projectId) {
            $project = $console->getUserProject($projectId);

            if (! $project) {
                throw new \Exception('Project for provided id not found.');
            }

            $output->writeln('Registering against existing project: ' . $project['name']);
        } else {
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
        }

        $path = $console->saveCreds([
            'user_token' => $userToken,
            'project_id' => $projectId,
            'user_id' => $userId
        ]);

        $output->writeln('<comment>Token saved: ' . $path . '</comment>');
    }
}
