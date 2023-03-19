<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Symfony\Component\Console\Output\OutputInterface;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use GuzzleHttp\Client;
use Exception;

class WebConsoleProcessor
{
    private $apiUrl = 'http://localhost:8000';//'http://bdd-analyser-api.inevitabletech.uk';

    private $consoleUrl = 'http://localhost:8080';//'https://bdd-analyser-console.inevitabletech.uk';

    private $apiVersion = 'v1';

    private $apiToken = '';

    private $tokenFile = 'token.txt';

    public $scanPath = '';

    public $creds = [];

    public function __construct(string $apiToken, Client $client)
    {
        if (! $apiToken) {
            throw new Exception('Unable to retrieve api_key. Have you set it in the config?');
        }

        $this->tokenFilePath = getcwd() . '/build';
        $this->apiToken = $apiToken;
        $this->client = $client;
        $this->loadCreds();
    }

    public function setToken(string $token)
    {
        $this->userToken = $token;
    }

    public function getUserId(): ?int
    {
        $response = $this->client->request(
            'GET',
            $this->getEndpoint('/user'),
            [
                'headers' => $this->getHeaders()
            ]
        );

        return $this->getContentIfSuccess($response, 'get-user-id')[0]['id'] ?? null;
    }

    public function getUserProjects(): ?array
    {
        $response = $this->client->request(
            'GET',
            $this->getEndpoint('/project'),
            [
                'headers' => $this->getHeaders()
            ]
        );

        return $this->getContentIfSuccess($response, 'get-user-projects') ?? null;
    }

    public function createProject(string $projectName, string $repoUrl, string $mainBranch, int $userId): ?int
    {
        $response = $this->client->request(
            'POST',
            $this->getEndpoint('/project'),
            [
                'json' => [
                    'user_id' => $userId,
                    'name' => $projectName,
                    'repo_url' => $repoUrl,
                    'main_branch' => $mainBranch
                ],
                'headers' => $this->getHeaders()
            ]
        );

        return $this->getContentIfSuccess($response, 'create-project')['id'] ?? null;
    }

    public function saveCreds(array $creds): string
    {
        if (! is_dir($this->tokenFilePath)) {
            mkdir($this->tokenFilePath, 0777, true);
        }

        return $this->saveToken(base64_encode(json_encode($creds)));
    }

    public function saveToken(string $token): string
    {
        file_put_contents($this->tokenFilePath . '/' . $this->tokenFile, $token);

        return $this->tokenFilePath . '/' . $this->tokenFile;
    }

    public function getCred(string $key)
    {
        if (! isset($this->creds[$key])) {
            throw new \Exception("Cred '$key' not found.");
        }

        return $this->creds[$key];
    }

    public function sendAnalysis(
        Entities\OutcomeCollection $outcomes,
        array $severities
    ): int {
        $activeRules = ArrayProcessor::cleanArray($outcomes->getSummary('activeRules'));
        $activeSteps = ArrayProcessor::cleanArray($outcomes->getSummary('activeSteps'));
        unset($outcomes->summary['activeRules']);
        unset($outcomes->summary['activeSteps']);

        $payload = [
            'json' => [
                'project_id' => $this->getCred('project_id'),
                'user_id' => $this->getCred('user_id'),
                'violations' => json_encode($this->cleanse($outcomes->getItems())),
                'summary' => json_encode($this->cleanse($outcomes->summary)),
                'active_rules' => json_encode($activeRules),
                'active_steps' => json_encode($activeSteps),
                'rules_version' => $this->getRulesVersion(),
                'severities' => json_encode($severities),
                'branch' => VersionControlProcessor::getBranch(),
                'commit_hash' => VersionControlProcessor::getCommitHash(),
                'run_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
            'headers' => $this->getHeaders()
        ];

        $response = $this->client->request(
            'POST',
            $this->getEndpoint('/analysis'),
            $payload
        );

        return $this->getContentIfSuccess($response, 'create-analysis')['id'] ?? null;
    }

    public function buildReportUrl(string $project, int $analysisId): string
    {
        return $this->consoleUrl . 'project/ ' . $project . ' /analysis/' . $analysisId;
    }

    public function printConsoleLink(OutputInterface $output, int $projectId, int $analysisId)
    {
        $output->writeln(sprintf(
            'Remote report: <comment>%s/project/%d/analysis/%d</comment>',
            $this->consoleUrl,
            $projectId,
            $analysisId
        ));
    }

    private function loadCreds(string $projectToken = null)
    {
        if (!file_exists($this->tokenFilePath . '/' . $this->tokenFile)) {
            return [];
        }

        $this->projectToken = file_get_contents($this->tokenFilePath . '/' . $this->tokenFile);

        $this->creds = json_decode(base64_decode($this->projectToken), true);
        $this->userToken = $this->creds['user_token'] ?? null;
    }

    private function cleanse(array $data): array
    {
        // Strip out path until project directory name.
        $projectPath = getcwd();

        return json_decode(str_replace(
            [$projectPath, str_replace('/', '\\/', $projectPath)],
            '',
            json_encode($data)
        ), true);
    }

    private function getRulesVersion(): string
    {
        return \Composer\InstalledVersions::getVersion('forceedge01/bdd-analyser-rules');
    }

    private function getHeaders(array $additionalHeaders = []): array
    {
        return array_merge([
            'Accept-Version' => $this->apiVersion,
            'Content-Type' => 'application/json',
            'api_token' => $this->apiToken,
            'user_token' => $this->userToken,
        ], $additionalHeaders);
    }

    private function getEndpoint(string $endpoint): string
    {
        return $this->apiUrl . $endpoint;
    }

    private function buildQueryParams(array $params): string
    {
        return '?' . http_build_query($params);
    }

    private function getContentIfSuccess($response, string $callId)
    {
        $data = json_decode($response->getBody()->getContents(), true);

        if ($data['success'] !== true) {
            throw new Exception("API call [$callId] failed, error: " . $data['message']);
        }

        return $data['data'];
    }
}
