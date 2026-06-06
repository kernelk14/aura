<?php

namespace AuraCore;

class Management
{
    protected $page;
    protected $pageId;
    protected $title = 'Management';
    protected $db;

    protected $pages = [
        'dashboard' => ['title' => 'Dashboard', 'icon' => '&#9632;', 'section' => 'Monitor'],
        'database' => ['title' => 'Database', 'icon' => '&#9776;', 'section' => 'Data'],
        'migrations' => ['title' => 'Migrations', 'icon' => '&#9654;', 'section' => 'Data'],
        'seeders' => ['title' => 'Seeders', 'icon' => '&#9830;', 'section' => 'Data'],
        'routes' => ['title' => 'Routes', 'icon' => '&#8594;', 'section' => 'System'],
        'logs' => ['title' => 'Logs', 'icon' => '&#9776;', 'section' => 'System'],
        'env' => ['title' => 'Environment', 'icon' => '&#9881;', 'section' => 'System'],
        'info' => ['title' => 'PHP Info', 'icon' => '&#9450;', 'section' => 'System'],
    ];

    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');

        $segments = explode('/', $uri);
        $this->page = $segments[1] ?? 'dashboard';
        $this->page = str_replace(['..', '/', '\\'], '', basename($this->page));

        if (!isset($this->pages[$this->page])) {
            $this->page = 'dashboard';
        }

        $this->pageId = $this->page;
        $this->title = $this->pages[$this->page]['title'];

        $this->tryConnectDb();

        ob_start();

        $method = 'page' . ucfirst($this->page);
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            $pageFile = SITE_PATH . 'pages/management/' . $this->page . '.php';
            if (file_exists($pageFile)) {
                require $pageFile;
            } else {
                echo '<div class="text-center py-5"><h2>Page not found</h2></div>';
            }
        }

        $content = ob_get_clean();

        $this->renderLayout($content);
    }

    protected function tryConnectDb()
    {
        try {
            $config = $this->loadDbConfig();
            if ($config) {
                require_once SYSTEM_PATH . 'core/database.php';
                require_once SYSTEM_PATH . 'core/query-builder.php';
                require_once SYSTEM_PATH . 'core/pagination.php';
                require_once SYSTEM_PATH . 'core/schema.php';
                $this->db = new Database($config);
            }
        } catch (\Exception $e) {
            $this->db = null;
        }
    }

    protected function loadDbConfig()
    {
        $dbConfigFile = SYSTEM_PATH . 'config/database.php';
        if (!file_exists($dbConfigFile)) {
            return null;
        }
        $config = require $dbConfigFile;
        return $config['default'] ?? null;
    }

    protected function renderLayout($content)
    {
        $page = $this->pageId;
        $title = $this->title;
        $pages = $this->pages;

        require SYSTEM_PATH . 'core/management-layout.php';
    }

    // --- Dashboard ---

    protected function pageDashboard()
    {
        require SITE_PATH . 'pages/management/dashboard.php';
    }

    // --- Database ---

    protected function pageDatabase()
    {
        $action = $_GET['action'] ?? 'tables';

        if ($action === 'query' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleQuery();
            return;
        }

        require SITE_PATH . 'pages/management/database.php';
    }

    protected function handleQuery()
    {
        $sql = $_POST['sql'] ?? '';
        $sql = trim($sql);

        if (empty($sql)) {
            echo '<div class="alert alert-warning">SQL query is empty.</div>';
            return;
        }

        if (!$this->db) {
            echo '<div class="alert alert-danger">Database not connected.</div>';
            return;
        }

        $pdo = $this->db->getPdo();

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();

            $selectKeywords = ['SELECT', 'SHOW', 'DESCRIBE', 'EXPLAIN', 'PRAGMA', 'WITH'];
            $isSelect = false;
            $upper = strtoupper(ltrim($sql));
            foreach ($selectKeywords as $kw) {
                if (strpos($upper, $kw) === 0) {
                    $isSelect = true;
                    break;
                }
            }

            if ($isSelect) {
                $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                echo '<div class="alert alert-success mb-3">' . count($results) . ' row(s) returned.</div>';
                if (!empty($results)) {
                    echo '<div class="overflow-auto"><table class="data-table data-table-bordered data-table-compact mb-0">';
                    echo '<thead><tr>';
                    foreach (array_keys($results[0]) as $col) {
                        echo '<th>' . htmlspecialchars($col) . '</th>';
                    }
                    echo '</tr></thead><tbody>';
                    foreach ($results as $row) {
                        echo '<tr>';
                        foreach ($row as $val) {
                            echo '<td>' . htmlspecialchars((string) $val) . '</td>';
                        }
                        echo '</tr>';
                    }
                    echo '</tbody></table></div>';
                } else {
                    echo '<p class="text-muted">No results.</p>';
                }
            } else {
                echo '<div class="alert alert-success">Query executed. ' . $stmt->rowCount() . ' row(s) affected.</div>';
            }
        } catch (\Exception $e) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }

    // --- Migrations ---

    protected function pageMigrations()
    {
        require SITE_PATH . 'pages/management/migrations.php';
    }

    // --- Seeders ---

    protected function pageSeeders()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_seeder'])) {
            $this->runSeederAction();
            return;
        }
        require SITE_PATH . 'pages/management/seeders.php';
    }

    protected function runSeederAction()
    {
        $seedersDir = SITE_PATH . 'seeders';
        if (!is_dir($seedersDir)) {
            echo '<div class="alert alert-warning">No seeders directory found.</div>';
            return;
        }

        $files = glob($seedersDir . '/*.php');
        sort($files);

        require_once SYSTEM_PATH . 'core/seeder.php';

        $db = $this->db;
        $seeded = 0;
        $errors = [];

        foreach ($files as $file) {
            $filename = basename($file);
            require_once $file;

            $base = preg_replace('/_seeder\.php$/', '', $filename);
            $base = preg_replace('/\.php$/', '', $base);
            $base = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $base)));
            $className = $base . 'Seeder';
            $className = str_replace('-', '_', $className);

            if (!class_exists($className)) {
                $errors[] = "Class not found: {$className}";
                continue;
            }

            try {
                $seeder = new $className();
                $seeder->setDb($db);
                $seeder->run();
                $seeded++;
            } catch (\Exception $e) {
                $errors[] = "{$filename}: " . $e->getMessage();
            }
        }

        echo '<div class="alert alert-success">Ran ' . $seeded . ' seeder(s).</div>';
        if (!empty($errors)) {
            foreach ($errors as $err) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($err) . '</div>';
            }
        }
    }

    // --- Routes ---

    protected function pageRoutes()
    {
        require SITE_PATH . 'pages/management/routes.php';
    }

    // --- Logs ---

    protected function pageLogs()
    {
        require SITE_PATH . 'pages/management/logs.php';
    }

    // --- Env ---

    protected function pageEnv()
    {
        require SITE_PATH . 'pages/management/env.php';
    }

    // --- Info ---

    protected function pageInfo()
    {
        require SITE_PATH . 'pages/management/info.php';
    }
}
