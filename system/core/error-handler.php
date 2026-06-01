<?php

namespace AuraCore;

class ErrorHandler
{
    protected static $debug = true;

    public static function register($debug = true)
    {
        static::$debug = $debug;
        set_error_handler([static::class, 'handleError']);
        set_exception_handler([static::class, 'handleException']);
        register_shutdown_function([static::class, 'handleShutdown']);
    }

    public static function handleError($level, $message, $file, $line)
    {
        if (!(error_reporting() & $level)) {
            return;
        }

        throw new \ErrorException($message, 0, $level, $file, $line);
    }

    public static function handleException($e)
    {
        http_response_code(500);

        if (php_sapi_name() === 'cli') {
            static::renderCli($e);
            return;
        }

        if (!static::$debug) {
            echo '<h1>500 Internal Server Error</h1><p>An unexpected error occurred.</p>';
            return;
        }

        static::renderHtml($e);
    }

    public static function handleShutdown()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            static::handleException(
                new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line'])
            );
        }
    }

    protected static function renderCli($e)
    {
        echo "\n";
        echo "  \033[31m" . get_class($e) . "\033[0m\n";
        echo "  \033[33m" . $e->getMessage() . "\033[0m\n";
        echo "  in \033[36m" . $e->getFile() . "\033[0m:\033[32m" . $e->getLine() . "\033[0m\n";
        echo "\n  \033[1mStack Trace:\033[0m\n";
        foreach ($e->getTrace() as $i => $trace) {
            $file = $trace['file'] ?? '[internal]';
            $line = $trace['line'] ?? '';
            $func = $trace['function'] ?? '';
            $class = $trace['class'] ?? '';
            echo sprintf("  %2d. %s%s%s(%s)\n      %s:%s\n",
                $i + 1,
                $class ? $class . '->' : '',
                $func,
                implode(', ', array_map(function ($a) { return is_scalar($a) ? $a : gettype($a); }, $trace['args'] ?? [])),
                $file,
                $line
            );
        }
        echo "\n";
    }

    protected static function renderHtml($e)
    {
        $code = $e->getCode();
        $message = htmlspecialchars($e->getMessage());
        $file = htmlspecialchars($e->getFile());
        $line = $e->getLine();
        $class = get_class($e);
        $trace = static::formatTrace($e);

        $codeLines = static::getCodeSnippet($e->getFile(), $e->getLine());

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Error - {$class}</title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #1a1a2e; color: #e0e0e0; padding: 30px; }
.error-container { max-width: 900px; margin: 0 auto; }
.error-header { background: #2d2d44; border-radius: 8px; padding: 20px 25px; margin-bottom: 20px; border-left: 4px solid #ff6b6b; }
.error-class { font-size: 14px; color: #ff6b6b; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
.error-message { font-size: 18px; color: #fff; font-weight: 600; margin-bottom: 6px; word-break: break-word; }
.error-location { font-size: 13px; color: #888; }
.error-location span { color: #4ecdc4; }
.section { background: #2d2d44; border-radius: 8px; padding: 20px 25px; margin-bottom: 20px; }
.section h3 { font-size: 14px; text-transform: uppercase; color: #aaa; letter-spacing: 1px; margin-bottom: 15px; }
.code-block { background: #1e1e32; border-radius: 6px; overflow: hidden; font-family: 'SF Mono', 'Fira Code', monospace; font-size: 13px; line-height: 1.6; }
.code-line { display: flex; padding: 0 15px; }
.code-line:hover { background: #2a2a44; }
.code-num { width: 50px; text-align: right; color: #555; padding-right: 15px; user-select: none; flex-shrink: 0; }
.code-src { flex: 1; white-space: pre; color: #ccc; }
.code-line.highlight { background: #3d2a2a; }
.code-line.highlight .code-num { color: #ff6b6b; }
.code-line.highlight .code-src { color: #ff6b6b; }
.trace-item { padding: 10px 0; border-bottom: 1px solid #3a3a52; font-size: 13px; line-height: 1.5; }
.trace-item:last-child { border-bottom: none; }
.trace-call { color: #4ecdc4; }
.trace-file { color: #888; font-size: 12px; }
.trace-args { color: #aaa; }
</style>
</head>
<body>
<div class="error-container">
    <div class="error-header">
        <div class="error-class">{$class}</div>
        <div class="error-message">{$message}</div>
        <div class="error-location">in <span>{$file}</span> line {$line}</div>
    </div>
    <div class="section">
        <h3>Source Code</h3>
        <div class="code-block">{$codeLines}</div>
    </div>
    <div class="section">
        <h3>Stack Trace</h3>
        {$trace}
    </div>
</div>
</body>
</html>
HTML;
        exit;
    }

    protected static function getCodeSnippet($file, $line, $padding = 6)
    {
        if (!file_exists($file)) {
            return '<div class="code-line"><span class="code-src">[file not found]</span></div>';
        }

        $lines = file($file);
        $start = max(0, $line - $padding - 1);
        $end = min(count($lines), $line + $padding);
        $html = '';

        for ($i = $start; $i < $end; $i++) {
            $num = $i + 1;
            $highlight = $num === $line ? ' highlight' : '';
            $src = htmlspecialchars(rtrim($lines[$i]));
            $html .= "<div class=\"code-line{$highlight}\">";
            $html .= "<span class=\"code-num\">{$num}</span>";
            $html .= "<span class=\"code-src\">{$src}</span>";
            $html .= '</div>';
        }

        return $html;
    }

    protected static function formatTrace($e)
    {
        $traces = $e->getTrace();
        $html = '';

        foreach ($traces as $i => $trace) {
            $file = $trace['file'] ?? '[internal function]';
            $line = $trace['line'] ?? '';
            $function = $trace['function'] ?? '';
            $class = $trace['class'] ?? '';
            $type = $trace['type'] ?? '';

            $call = $class ? $class . $type . $function : $function;

            $args = array_map(function ($arg) {
                if (is_object($arg)) {
                    return get_class($arg);
                }
                if (is_array($arg)) {
                    return 'Array[' . count($arg) . ']';
                }
                if (is_string($arg)) {
                    return "'" . htmlspecialchars(substr($arg, 0, 50)) . "'";
                }
                if (is_null($arg)) {
                    return 'null';
                }
                if (is_bool($arg)) {
                    return $arg ? 'true' : 'false';
                }
                return (string) $arg;
            }, $trace['args'] ?? []);

            $html .= '<div class="trace-item">';
            $html .= '<div class="trace-call">#' . ($i + 1) . ' ' . htmlspecialchars($call) . '(' . htmlspecialchars(implode(', ', $args)) . ')</div>';
            $html .= '<div class="trace-file">' . htmlspecialchars($file) . ($line ? ':' . $line : '') . '</div>';
            $html .= '</div>';
        }

        return $html;
    }
}
