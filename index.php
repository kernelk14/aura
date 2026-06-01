<?php

define('SYSTEM_PATH', __DIR__ . '/system/');
define('SITE_PATH', __DIR__ . '/site/');

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

$coreFiles = [
    'core/base.php',
    'core/controller.php',
    'core/model.php',
    'core/database.php',
    'core/query-builder.php',
    'core/pagination.php',
    'core/schema.php',
    'core/seeder.php',
    'core/migration.php',
    'core/router.php',
    'core/request.php',
    'core/response.php',
    'core/session.php',
    'core/dotenv.php',
    'core/logger.php',
    'core/event.php',
    'core/container.php',
    'core/validator.php',
    'core/auth.php',
    'core/middleware-pipeline.php',
    'core/error-handler.php',
    'core/management.php',
    'helpers/url_helper.php',
];

foreach ($coreFiles as $file) {
    $path = SYSTEM_PATH . $file;
    if (file_exists($path)) {
        require_once $path;
    }
}

spl_autoload_register('aurora_autoload');

function aurora_autoload($class)
{
    $classPath = str_replace('\\', '/', $class);

    $nsMappings = [
        'AuraCore/' => ['base' => SYSTEM_PATH . 'core/', 'prefix' => ''],
        'SiteModels/' => ['base' => SITE_PATH, 'prefix' => 'models/'],
        'SiteControllers/' => ['base' => SITE_PATH, 'prefix' => 'controllers/'],
        'SiteMiddleware/' => ['base' => SITE_PATH, 'prefix' => 'middleware/'],
    ];

    $resolved = false;
    foreach ($nsMappings as $ns => $mapping) {
        if (strpos($classPath, $ns) === 0) {
            $relative = substr($classPath, strlen($ns));
            $parts = explode('/', $relative);
            $className = lcfirst(array_pop($parts));

            $snakeName = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $className));
            $kebabName = strtolower(preg_replace('/(?<!^)([A-Z])/', '-$1', $className));

            $variants = array_unique([
                $className,
                strtolower($className),
                $snakeName,
                $kebabName,
            ]);

            foreach ($variants as $v) {
                $file = $mapping['base'] . $mapping['prefix'] . $v . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
            $resolved = true;
        }
    }

    if ($resolved) {
        return;
    }

    $parts = explode('/', $classPath);
    $className = lcfirst(array_pop($parts));
    $nsPrefix = !empty($parts) ? implode('/', $parts) . '/' : '';

    $snakeName = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $className));
    $kebabName = strtolower(preg_replace('/(?<!^)([A-Z])/', '-$1', $className));

    $variants = array_unique([
        $nsPrefix . $className,
        $nsPrefix . strtolower($className),
        $nsPrefix . $snakeName,
        $nsPrefix . $kebabName,
        $snakeName,
        $kebabName,
        strtolower($className),
    ]);

    foreach ([SYSTEM_PATH, SITE_PATH] as $base) {
        foreach (['', 'models/', 'controllers/', 'middleware/'] as $sub) {
            foreach ($variants as $v) {
                $file = $base . $sub . $v . '.php';
                if (file_exists($file)) {
                    require_once $file;
                    return;
                }
            }
        }
    }
}

AuraCore\ErrorHandler::register(true);

try {
    $dotenvFile = __DIR__ . '/.env';
    if (file_exists($dotenvFile)) {
        AuraCore\DotEnv::load($dotenvFile);
    }
} catch (\Exception $e) {
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = '/' . trim($uri, '/');

if (preg_match('#^/management(/|$)#', $uri)) {
    $mgmt = new AuraCore\Management();
    $mgmt->run();
    exit;
}

$router = require_once SYSTEM_PATH . 'config/routes.php';

if (!$router->dispatch()) {
    http_response_code(404);

    if (class_exists('AuraCore\\Request') && (new AuraCore\Request())->ajax()) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not Found']);
        exit;
    }

    header("HTTP/1.0 404 Not Found");

    $viewFile = SITE_PATH . 'views/404.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo '<!DOCTYPE html><html><head><title>404 - Not Found</title>';
        echo '<style>body{font-family:sans-serif;display:flex;justify-content:center;align-items:center;height:100vh;margin:0;background:#1a1a2e;color:#e0e0e0}';
        echo '.error{text-align:center}h1{font-size:72px;margin:0;color:#ff6b6b}p{font-size:18px;color:#888}</style></head>';
        echo '<body><div class="error"><h1>404</h1><p>Page not found</p></div></body></html>';
    }
    exit;
}
