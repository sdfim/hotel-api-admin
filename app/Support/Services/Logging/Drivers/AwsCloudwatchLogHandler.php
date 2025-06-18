<?php

namespace App\Support\Services\Logging\Drivers;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use DateTime;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class AwsCloudwatchLogHandler extends AbstractProcessingHandler
{
    public const EVENT_SIZE_LIMIT = 262118; // 256 KB - reserved 26

    public const RPS_LIMIT = 5;

    private int $batchSize;

    private array $buffer = [];

    private CloudWatchLogsClient $client;

    private bool $createGroup;

    private int $currentDataAmount = 0;

    private int $dataAmountLimit = 1048576;

    private string $group;

    private bool $initialized = false;

    private int $remainingRequests = self::RPS_LIMIT;

    private int $retention;

    private DateTime $savedTime;

    private string $sequenceToken;

    private string $stream;

    private array $tags;

    public function __construct(
        string $region,
        string $version,
        string $group,
        string $stream,
        int $retention = 14,
        int $batchSize = 1000,
        array $tags = [],
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        bool $createGroup = true
    ) {
        if ($batchSize > 10000) {
            throw new InvalidArgumentException('Batch size can not be greater than 10000');
        }

        $this->client = new CloudWatchLogsClient([
            'region' => $region,
            'version' => $version,
            // credentials удалены — AWS SDK сам использует IAM роль
        ]);

        $this->group = $group;
        $this->stream = $stream;
        $this->retention = $retention;
        $this->batchSize = $batchSize;
        $this->tags = $tags;
        $this->createGroup = $createGroup;

        parent::__construct($level, $bubble);

        $this->savedTime = new DateTime;
    }

    public function close(): void
    {
        $this->flushBuffer();
    }

    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter('%channel%: %level_name%: %message% %context% %extra%', null, false, true, true);
    }

    protected function write(LogRecord $record): void
    {
        $records = $this->formatRecords($record);

        foreach ($records as $item) {
            if ($this->currentDataAmount + $this->getMessageSize($item) >= $this->dataAmountLimit) {
                $this->flushBuffer();
            }

            $this->addToBuffer($item);

            if (count($this->buffer) >= $this->batchSize) {
                $this->flushBuffer();
            }
        }
    }

    private function addToBuffer(array $record): void
    {
        $this->currentDataAmount += $this->getMessageSize($record);
        $this->buffer[] = $record;
    }

    private function checkThrottle(): void
    {
        $current = new DateTime;
        $diff = $current->diff($this->savedTime, true)->s;
        $sameSecond = $diff === 0;

        if ($sameSecond && $this->remainingRequests > 0) {
            $this->remainingRequests--;
        } elseif ($sameSecond && $this->remainingRequests === 0) {
            sleep(1);
            $this->remainingRequests = self::RPS_LIMIT;
        } elseif (! $sameSecond) {
            $this->remainingRequests = self::RPS_LIMIT;
        }

        $this->savedTime = new DateTime;
    }

    private function flushBuffer(): void
    {
        if (! empty($this->buffer)) {
            if (! $this->initialized) {
                $this->initialize();
            }

            try {
                $this->send($this->buffer);
            } catch (CloudWatchLogsException $e) {
                try {
                    $this->refreshSequenceToken();
                    $this->send($this->buffer);
                } catch (CloudWatchLogsException $e) {
                    $this->refreshSequenceToken();
                    $this->send($this->buffer);
                }
            }

            $this->buffer = [];
            $this->currentDataAmount = 0;
        }
    }

    private function formatRecords(LogRecord $entry): array
    {
        $entries = str_split($entry->formatted, self::EVENT_SIZE_LIMIT);
        $timestamp = $entry->datetime->format('U.u') * 1000;
        $records = [];

        foreach ($entries as $item) {
            $records[] = [
                'message' => $item,
                'timestamp' => $timestamp,
            ];
        }

        return $records;
    }

    private function getMessageSize(array $record): int
    {
        return strlen($record['message']) + 26;
    }

    private function initialize(): void
    {
        if ($this->createGroup) {
            $this->initializeGroup();
        }

        $this->refreshSequenceToken();
    }

    private function initializeGroup(): void
    {
        $existingGroups = $this->client->describeLogGroups([
            'logGroupNamePrefix' => $this->group,
        ])->get('logGroups');

        $existingGroupsNames = array_map(
            static fn ($group) => $group['logGroupName'],
            $existingGroups
        );

        if (! in_array($this->group, $existingGroupsNames, true)) {
            $args = ['logGroupName' => $this->group];

            if (! empty($this->tags)) {
                $args['tags'] = $this->tags;
            }

            $this->client->createLogGroup($args);

            if ($this->retention !== null) {
                $this->client->putRetentionPolicy([
                    'logGroupName' => $this->group,
                    'retentionInDays' => $this->retention,
                ]);
            }
        }
    }

    private function refreshSequenceToken(): void
    {
        $existingStreams = $this->client->describeLogStreams([
            'logGroupName' => $this->group,
            'logStreamNamePrefix' => $this->stream,
        ])->get('logStreams');

        $existingStreamsNames = array_map(function ($stream) {
            if ($stream['logStreamName'] === $this->stream && isset($stream['uploadSequenceToken'])) {
                $this->sequenceToken = $stream['uploadSequenceToken'];
            }

            return $stream['logStreamName'];
        }, $existingStreams);

        if (! in_array($this->stream, $existingStreamsNames, true)) {
            $this->client->createLogStream([
                'logGroupName' => $this->group,
                'logStreamName' => $this->stream,
            ]);
        }

        $this->initialized = true;
    }

    private function send(array $entries): void
    {
        usort($entries, static fn ($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        $data = [
            'logGroupName' => $this->group,
            'logStreamName' => $this->stream,
            'logEvents' => $entries,
        ];

        if (! empty($this->sequenceToken)) {
            $data['sequenceToken'] = $this->sequenceToken;
        }

        $this->checkThrottle();

        $response = $this->client->putLogEvents($data);

        $this->sequenceToken = $response->get('nextSequenceToken');
    }
}
