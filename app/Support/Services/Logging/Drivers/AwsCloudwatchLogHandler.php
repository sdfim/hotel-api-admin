<?php

namespace App\Support\Services\Logging\Drivers;

use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Aws\CloudWatchLogs\Exception\CloudWatchLogsException;
use DateTime;
use Exception;
use InvalidArgumentException;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class AwsCloudwatchLogHandler extends AbstractProcessingHandler
{
    /**
     * Event size limit (https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/cloudwatch_limits_cwl.html)
     *
     * @var int
     */
    public const EVENT_SIZE_LIMIT = 262118; // 262144 - reserved 26

    /**
     * Requests per second limit (https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/cloudwatch_limits_cwl.html)
     */
    public const RPS_LIMIT = 5;

    private int $batchSize;

    private array $buffer = [];

    private CloudWatchLogsClient $client;

    private bool $createGroup;

    private int $currentDataAmount = 0;

    /**
     * Data amount limit (http://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_PutLogEvents.html)
     */
    private int $dataAmountLimit = 1048576;

    private string $group;

    private bool $initialized = false;

    private int $remainingRequests = self::RPS_LIMIT;

    private int $retention;

    private DateTime $savedTime;

    private string $sequenceToken;

    private string $stream;

    private array $tags;

    /**
     * CloudWatchLogs constructor.
     *
     * @param  CloudWatchLogsClient  $client
     *
     *  Log group names must be unique within a region for an AWS account.
     *  Log group names can be between 1 and 512 characters long.
     *  Log group names consist of the following characters: a-z, A-Z, 0-9, '_' (underscore), '-' (hyphen),
     * '/' (forward slash), and '.' (period).
     * @param  string  $group
     *
     *  Log stream names must be unique within the log group.
     *  Log stream names can be between 1 and 512 characters long.
     *  The ':' (colon) and '*' (asterisk) characters are not allowed.
     * @param  int  $level
     *
     * @throws Exception
     */
    public function __construct(
        CloudWatchLogsClient $client,
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

        $this->client = $client;
        $this->group = $group;
        $this->stream = $stream;
        $this->retention = $retention;
        $this->batchSize = $batchSize;
        $this->tags = $tags;
        $this->createGroup = $createGroup;

        parent::__construct($level, $bubble);

        $this->savedTime = new DateTime();
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->flushBuffer();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        return new LineFormatter('%channel%: %level_name%: %message% %context% %extra%', null, false, true, true);
    }

    /**
     * {@inheritdoc}
     */
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
        $current = new DateTime();
        $diff = $current->diff($this->savedTime)->s;
        $sameSecond = $diff === 0;

        if ($sameSecond && $this->remainingRequests > 0) {
            $this->remainingRequests--;
        } elseif ($sameSecond && $this->remainingRequests === 0) {
            sleep(1);
            $this->remainingRequests = self::RPS_LIMIT;
        } elseif (! $sameSecond) {
            $this->remainingRequests = self::RPS_LIMIT;
        }

        $this->savedTime = new DateTime();
    }

    private function flushBuffer(): void
    {
        if (! empty($this->buffer)) {
            if ($this->initialized === false) {
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

    /**
     * Event size in the batch can not be bigger than 256 KB
     * https://docs.aws.amazon.com/AmazonCloudWatch/latest/logs/cloudwatch_limits_cwl.html
     */
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

    /**
     * http://docs.aws.amazon.com/AmazonCloudWatchLogs/latest/APIReference/API_PutLogEvents.html
     */
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
        $existingGroups = $this->client->describeLogGroups(
            [
                'logGroupNamePrefix' => $this->group,
            ]
        )->get('logGroups');

        $existingGroupsNames = array_map(
            static function ($group) {
                return $group['logGroupName'];
            },
            $existingGroups
        );

        if (! in_array($this->group, $existingGroupsNames, true)) {
            $createLogGroupArguments = ['logGroupName' => $this->group];

            if (! empty($this->tags)) {
                $createLogGroupArguments['tags'] = $this->tags;
            }

            $this->client->createLogGroup($createLogGroupArguments);

            if ($this->retention !== null) {
                $this->client->putRetentionPolicy(
                    [
                        'logGroupName' => $this->group,
                        'retentionInDays' => $this->retention,
                    ]
                );
            }
        }
    }

    private function refreshSequenceToken(): void
    {
        $existingStreams = $this->client->describeLogStreams(
            [
                'logGroupName' => $this->group,
                'logStreamNamePrefix' => $this->stream,
            ]
        )->get('logStreams');

        $existingStreamsNames = array_map(
            function ($stream) {
                if ($stream['logStreamName'] === $this->stream && isset($stream['uploadSequenceToken'])) {
                    $this->sequenceToken = $stream['uploadSequenceToken'];
                }

                return $stream['logStreamName'];
            },
            $existingStreams
        );

        if (! in_array($this->stream, $existingStreamsNames, true)) {
            $this->client->createLogStream(
                [
                    'logGroupName' => $this->group,
                    'logStreamName' => $this->stream,
                ]
            );
        }

        $this->initialized = true;
    }

    /**
     * The batch of events must satisfy the following constraints:
     *  - The maximum batch size is 1,048,576 bytes, and this size is calculated as the sum of all event messages in
     * UTF-8, plus 26 bytes for each log event.
     *  - None of the log events in the batch can be more than 2 hours in the future.
     *  - None of the log events in the batch can be older than 14 days or the retention period of the log group.
     *  - The log events in the batch must be in chronological ordered by their timestamp (the time the event occurred,
     * expressed as the number of milliseconds since Jan 1, 1970 00:00:00 UTC).
     *  - The maximum number of log events in a batch is 10,000.
     *  - A batch of log events in a single request cannot span more than 24 hours. Otherwise, the operation fails.
     *
     *
     * @throws CloudWatchLogsException
     */
    private function send(array $entries): void
    {
        // AWS expects to receive entries in chronological order...
        usort($entries, static function (array $a, array $b) {
            if ($a['timestamp'] < $b['timestamp']) {
                return -1;
            }

            if ($a['timestamp'] > $b['timestamp']) {
                return 1;
            }

            return 0;
        });

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
