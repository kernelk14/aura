<?php

namespace AuraCore;

class Logger
{
    protected $path;
    protected $minLevel;

    protected $levels = [
        'DEBUG' => 0,
        'INFO' => 1,
        'NOTICE' => 2,
        'WARNING' => 3,
        'ERROR' => 4,
        'CRITICAL' => 5,
    ];

    public function __construct($path = null, $minLevel = 'DEBUG')
    {
        $this->path = $path ?? dirname(__DIR__, 2) . '/storage/logs';
        $this->minLevel = strtoupper($minLevel);

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    public function debug($message, array $context = [])
    {
        $this->log('DEBUG', $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log('INFO', $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log('NOTICE', $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log('WARNING', $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log('ERROR', $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log('CRITICAL', $message, $context);
    }

    protected function log($level, $message, array $context = [])
    {
        if (($this->levels[$level] ?? 0) < ($this->levels[$this->minLevel] ?? 0)) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $line = "[{$date}] {$level}: {$message}{$contextStr}" . PHP_EOL;

        $file = $this->path . '/' . date('Y-m-d') . '.log';
        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
