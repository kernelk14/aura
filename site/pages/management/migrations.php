<?php
$db = $this->db;
$driver = $db ? $db->getDriver() : 'mysql';
$pdo = $db ? $db->getPdo() : null;
$migrationsDir = SITE_PATH . 'migrations';
$migrations = [];
$runMigrations = [];

if ($db && is_dir($migrationsDir)) {
    try {
        $pdo->query("SELECT 1 FROM migrations LIMIT 1");
        $stmt = $pdo->query("SELECT migration, batch, executed_at FROM migrations ORDER BY migration");
        $runMigrations = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $runNames = array_column($runMigrations, 'migration');
    } catch (\Exception $e) { $runNames = []; }

    $files = glob($migrationsDir . '/*.php');
    sort($files);
    foreach ($files as $file) {
        $filename = basename($file);
        $base = preg_replace('/\.php$/', '', $filename);
        $runInfo = null;
        foreach ($runMigrations as $rm) {
            if ($rm['migration'] === $base) { $runInfo = $rm; break; }
        }
        $migrations[] = ['file' => $filename, 'name' => $base, 'run' => $runInfo];
    }
}
?>

<div class="d-flex gap-2 mb-3">
    <form method="post" action="/management/migrations" style="display:inline">
        <input type="hidden" name="action" value="migrate">
        <button type="submit" class="btn btn-primary btn-sm">Run Migrations</button>
    </form>
    <form method="post" action="/management/migrations" style="display:inline">
        <input type="hidden" name="action" value="rollback">
        <button type="submit" class="btn btn-warning btn-sm">Rollback</button>
    </form>
    <form method="post" action="/management/migrations" style="display:inline" onsubmit="return confirm('Reset all migrations? This will drop all tables.');">
        <input type="hidden" name="action" value="reset">
        <button type="submit" class="btn btn-danger btn-sm">Reset</button>
    </form>
</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if (!file_exists(SYSTEM_PATH . 'core/migration.php')) {
        echo '<div class="alert alert-danger">Migration base class not found.</div>';
    } elseif (!$db) {
        echo '<div class="alert alert-warning">Database not connected.</div>';
    } else {
        ob_start();
        require_once SYSTEM_PATH . 'core/migration.php';
        $pdo = $db->getPdo();

        if ($action === 'migrate') {
            $files = glob($migrationsDir . '/*.php');
            sort($files);
            $count = 0;
            foreach ($files as $file) {
                $filename = basename($file);
                $base = preg_replace('/\.php$/', '', $filename);
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM migrations WHERE migration = ?");
                $stmt->execute([$base]);
                if ($stmt->fetchColumn() > 0) continue;
                require_once $file;
                $className = \AuraCore\Migration::resolveClass($base, $filename);
                if (!$className || !class_exists($className)) continue;
                $inTransaction = false;
                try {
                    if (!$pdo->inTransaction()) {
                        $pdo->beginTransaction();
                        $inTransaction = true;
                    }
                    $mig = new $className();
                    $mig->setDb($db);
                    $mig->up();
                    $stmt = $pdo->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
                    $batch = (int) $pdo->query("SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations")->fetchColumn();
                    $stmt->execute([$base, $batch]);
                    if ($inTransaction) {
                        $pdo->commit();
                    }
                    $count++;
                } catch (\Exception $e) {
                    if ($inTransaction && $pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    echo '<div class="alert alert-danger">' . htmlspecialchars($filename . ': ' . $e->getMessage()) . '</div>';
                }
            }
            echo '<div class="alert alert-success">' . $count . ' migration(s) run.</div>';
        } elseif ($action === 'rollback') {
            $stmt = $pdo->query("SELECT COALESCE(MAX(batch), 0) FROM migrations");
            $lastBatch = (int) $stmt->fetchColumn();
            if ($lastBatch > 0) {
                $stmt = $pdo->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY migration DESC");
                $stmt->execute([$lastBatch]);
                $toRollback = $stmt->fetchAll(\PDO::FETCH_COLUMN);
                $count = 0;
                foreach ($toRollback as $base) {
                    $file = $migrationsDir . '/' . $base . '.php';
                    if (!file_exists($file)) continue;
                    require_once $file;
                    $className = \AuraCore\Migration::resolveClass($base, basename($file));
                    if (!$className || !class_exists($className)) continue;
                    $inTransaction = false;
                    try {
                        if (!$pdo->inTransaction()) {
                            $pdo->beginTransaction();
                            $inTransaction = true;
                        }
                        $mig = new $className();
                        $mig->setDb($db);
                        $mig->down();
                        $pdo->prepare("DELETE FROM migrations WHERE migration = ?")->execute([$base]);
                        if ($inTransaction) {
                            $pdo->commit();
                        }
                        $count++;
                    } catch (\Exception $e) {
                        if ($inTransaction && $pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        echo '<div class="alert alert-danger">' . htmlspecialchars($base . ': ' . $e->getMessage()) . '</div>';
                    }
                }
                echo '<div class="alert alert-warning">' . $count . ' migration(s) rolled back.</div>';
            } else {
                echo '<div class="alert alert-info">Nothing to roll back.</div>';
            }
        } elseif ($action === 'reset') {
            $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY batch DESC, migration DESC");
            $all = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            $count = 0;
            foreach ($all as $base) {
                $file = $migrationsDir . '/' . $base . '.php';
                if (!file_exists($file)) continue;
                require_once $file;
                $className = \AuraCore\Migration::resolveClass($base, basename($file));
                if (!$className || !class_exists($className)) continue;
                try { $mig = new $className(); $mig->setDb($db); $mig->down(); $count++; } catch (\Exception $e) {}
            }
            $pdo->exec("DELETE FROM migrations");
            echo '<div class="alert alert-danger">' . $count . ' migration(s) reset.</div>';
        }
        echo ob_get_clean();
    }
}
?>

<div class="card card-dark">
    <div class="card-header fw-bold">Migration Status</div>
    <?php if (empty($migrations)): ?>
        <div class="card-body text-muted">No migration files found in site/migrations/</div>
    <?php else: ?>
        <div class="overflow-auto">
            <table class="data-table data-table-bordered data-table-compact mb-0">
                <thead><tr><th>Migration</th><th>Batch</th><th>Ran At</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($migrations as $m): ?>
                        <tr>
                            <td><?= htmlspecialchars($m['name']) ?></td>
                            <?php if ($m['run']): ?>
                                <td><?= htmlspecialchars((string) $m['run']['batch']) ?></td>
                                <td><?= htmlspecialchars((string) ($m['run']['executed_at'] ?? '-')) ?></td>
                                <td><span class="badge bg-success">Ran</span></td>
                            <?php else: ?>
                                <td>-</td><td>-</td>
                                <td><span class="badge bg-warning text-dark">Pending</span></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
