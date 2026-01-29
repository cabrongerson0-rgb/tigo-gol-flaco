<?php

declare(strict_types=1);

namespace App\Core;

class Logger
{
    private static ?Logger $instance = null;
    private string $logFile;
    private string $level;
    
    private const LEVELS = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3, 'critical' => 4];

    private function __construct(string $logPath)
    {
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        $this->logFile = $logPath . '/app.log';
        $this->level = 'debug';
    }

    public static function getInstance(string $logPath): Logger
    {
        if (self::$instance === null) {
            self::$instance = new self($logPath);
        }
        return self::$instance;
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    private function log(string $level, string $message, array $context = []): void
    {
        if (self::LEVELS[$level] < self::LEVELS[$this->level]) {
            return;
        }

        $logMessage = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $message,
            $context ? ' ' . json_encode($context) : ''
        );

        file_put_contents($this->logFile, $logMessage, FILE_APPEND);
    }
}
