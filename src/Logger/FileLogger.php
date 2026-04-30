<?php

declare(strict_types=1);

namespace App\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Stringable;

class FileLogger extends AbstractLogger
{
    private readonly string $logPath;

    private const LEVEL_ORDER = [
        LogLevel::DEBUG => 0,
        LogLevel::INFO => 1,
        LogLevel::NOTICE => 2,
        LogLevel::WARNING => 3,
        LogLevel::ERROR => 4,
        LogLevel::CRITICAL => 5,
        LogLevel::ALERT => 6,
        LogLevel::EMERGENCY => 7,
    ];

    /**
     * @param string $logPath  Absolute path to the log file.
     * @param string $minLevel Minimum PSR-3 log level to write.
     */
    public function __construct(
        string $logPath,
        private readonly string $minLevel = LogLevel::DEBUG,
    ) {
        $this->logPath = $logPath;
    }

    /**
     * @param mixed               $level
     * @param string|Stringable   $message
     * @param array<string, mixed> $context
     */
    public function log(mixed $level, string|Stringable $message, array $context = []): void
    {
        $levelStr = (string) $level;

        if (!$this->isLevelEnabled($levelStr)) {
            return;
        }

        $contextJson = $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '';
        $line = sprintf(
            "[%s] [%s] %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($levelStr),
            (string) $message,
            $contextJson,
        );

        file_put_contents($this->logPath, $line, FILE_APPEND | LOCK_EX);
    }

    private function isLevelEnabled(string $level): bool
    {
        $min = self::LEVEL_ORDER[$this->minLevel] ?? 0;
        $current = self::LEVEL_ORDER[$level] ?? 0;

        return $current >= $min;
    }
}
