<?php

declare(strict_types = 1);

namespace Forceedge01\BDDStaticAnalyser\Processor;

use Symfony\Component\Console\Output\OutputInterface;
use Forceedge01\BDDStaticAnalyserRules\Entities;
use GuzzleHttp\Client;
use Exception;

class WebConsoleProcessor
{
    private $apiUrl = 'http://localhost:8080';//'http://bdd-analyser-api.inevitabletech.uk';

    private $consoleUrl = 'https://bdd-analyser-console.inevitabletech.uk';

    private $apiVersion = 'v1';

    private $apiToken = '';

    private $tokenFile = 'token.txt';

    public $scanPath = '';

    public function __construct(string $apiToken, Client $client)
    {
        if (! $apiToken) {
            throw new Exception('Unable to retrieve api_key. Have you set it in the config?');
        }

        $this->tokenFilePath = getcwd() . '/build';
        $this->apiToken = $apiToken;
        $this->client = $client;
    }

    public function createUser(string $firstname, string $lastname, string $email): ?int
    {
        $response = $this->client->request(
            'POST',
            $this->getEndpoint('/user'),
            [
                'json' => [
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email
                ],
                'headers' => $this->getHeaders()
            ]
        );

        try {
            return $this->getContentIfSuccess($response, 'create-user')['user_id'];
        } catch (Exception $e) {
            $response = $this->client->request(
                'GET',
                $this->getEndpoint('/user/id/' . $email),
                [
                    'headers' => $this->getHeaders()
                ]
            );

            return $this->getContentIfSuccess($response, 'get-user-id')[0]['user_id'] ?? null;
        }
    }

    public function createProject(string $token, string $projectName, int $userId): ?int
    {
        $response = $this->client->request(
            'POST',
            $this->getEndpoint('/project'),
            [
                'json' => [
                    'user_id' => $userId,
                    'name' => $projectName
                ],
                'headers' => $this->getHeaders(['user_token' => $token])
            ]
        );

        return $this->getContentIfSuccess($response, 'create-project')['id'] ?? null;
    }

    public function createToken(int $userId): ?string
    {
        $response = $this->client->request(
            'POST',
            $this->getEndpoint('/token'),
            [
                'json' => [
                    'user_id' => $userId
                ],
                'headers' => $this->getHeaders()
            ]
        );

        return $this->getContentIfSuccess($response, 'create-token')['token'];
    }

    public function saveTokenDetails(string $token, int $projectId): string
    {
        if (! is_dir($this->tokenFilePath)) {
            mkdir($this->tokenFilePath, 0777, true);
        }

        file_put_contents($this->tokenFilePath . '/' . $this->tokenFile, base64_encode(json_encode([
            'userToken' => $token,
            'projectId' => $projectId
        ])));

        return $this->tokenFilePath . '/' . $this->tokenFile;
    }

    public function getTokenDetails(): array
    {
        return json_decode(base64_decode(file_get_contents($this->tokenFilePath . '/' . $this->tokenFile)), true);
    }

    public function sendAnalysis(
        Entities\OutcomeCollection $outcomes,
        array $severities,
        string $userToken,
        int $projectId
    ): int {
        $activeRules = ArrayProcessor::cleanArray($outcomes->getSummary('activeRules'));
        $activeSteps = ArrayProcessor::cleanArray($outcomes->getSummary('activeSteps'));
        unset($outcomes->summary['activeRules']);
        unset($outcomes->summary['activeSteps']);

        $payload = [
            'json' => [
                'project_id' => $projectId,
                'violations' => $this->cleanse($outcomes->getItems()),
                'summary' => $this->cleanse($outcomes->summary),
                'active_rules' => $activeRules,
                'active_steps' => $activeSteps,
                'rules_version' => $this->getRulesVersion(),
                'severities' => json_encode($severities),
                'branch' => '',
                'commit_hash' => '',
                'run_at' => (new \DateTime())->format('Y-m-d H:i:s'),
            ],
            'headers' => $this->getHeaders(['user_token' => $userToken])
        ];

        $response = $this->client->request(
            'POST',
            $this->getEndpoint('/analysis'),
            $payload
        );

        return $this->getContentIfSuccess($response, 'create-analysis')['id'] ?? null;
    }

    private function cleanse(array $data): array
    {
        // Strip out path until project directory name.
        $cwd = getcwd();
        $projectPath = dirname($cwd);

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

    private function getHeaders(array $additionalHeaders = []): array
    {
        return array_merge([
            'Accept-Version' => $this->apiVersion,
            'Content-Type' => 'application/json',
            'api_token' => $this->apiToken
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
