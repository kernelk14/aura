<?php
$db = $this->db;
$tables = [];
$totalRows = 0;
$totalDataSize = 0;
$totalIndexSize = 0;
$pdo = null;

if ($db) {
    try {
        $pdo = $db->getPdo();
        $res = $pdo->query("SHOW TABLE STATUS");
        $tables = $res->fetchAll(\PDO::FETCH_ASSOC);
    } catch (\Exception $e) {}
}

foreach ($tables as $t) {
    $totalRows += (int)($t['Rows'] ?? 0);
    $totalDataSize += (int)($t['Data_length'] ?? 0);
    $totalIndexSize += (int)($t['Index_length'] ?? 0);
}
$totalDbSize = $totalDataSize + $totalIndexSize;
$totalTables = count($tables);

// Server stats
$phpVersion = phpversion();
$phpMemory = memory_get_usage(true);
$phpPeakMemory = memory_get_peak_usage(true);
$phpExtCount = count(get_loaded_extensions());
$serverSoft = $_SERVER['SERVER_SOFTWARE'] ?? 'Built-in';
$diskFree = @disk_free_space(__DIR__);
$diskTotal = @disk_total_space(__DIR__);
$diskUsed = $diskTotal ? $diskTotal - $diskFree : 0;
$diskPct = $diskTotal ? round(($diskUsed / $diskTotal) * 100, 1) : 0;
$loadAvgs = function_exists('sys_getloadavg') ? sys_getloadavg() : null;
$requestTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? null;

// App file counts
$controllerCount = count(glob(SITE_PATH . 'controllers/*.php'));
$modelCount = count(glob(SITE_PATH . 'models/*.php'));
$middlewareCount = count(glob(SITE_PATH . 'middleware/*.php'));
$migrationFiles = glob(SITE_PATH . 'migrations/*.php');
$migrationCount = count($migrationFiles);

// Migration status
$migratedCount = 0;
if ($db) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) FROM migrations");
        $migratedCount = (int)$stmt->fetchColumn();
    } catch (\Exception $e) {}
}

// Format bytes helper
function mgmt_format_bytes($bytes, $dec = 1) {
    if ($bytes >= 1073741824) return round($bytes / 1073741824, $dec) . ' GiB';
    if ($bytes >= 1048576) return round($bytes / 1048576, $dec) . ' MiB';
    if ($bytes >= 1024) return round($bytes / 1024, $dec) . ' KiB';
    return $bytes . ' B';
}
?>
<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card card-dark text-center"><div class="card-body"><div class="text-info fw-bold" style="font-size:1.75rem"><?= $totalTables ?></div><div class="text-muted text-xs text-uppercase">Tables</div></div></div></div>
    <div class="col-md-3"><div class="card card-dark text-center"><div class="card-body"><div class="text-info fw-bold" style="font-size:1.75rem"><?= number_format($totalRows) ?></div><div class="text-muted text-xs text-uppercase">Total Rows</div></div></div></div>
    <div class="col-md-3"><div class="card card-dark text-center"><div class="card-body"><div class="text-success fw-bold" style="font-size:1.75rem"><?= mgmt_format_bytes($totalDbSize) ?></div><div class="text-muted text-xs text-uppercase">DB Size</div></div></div></div>
    <div class="col-md-3"><div class="card card-dark text-center"><div class="card-body"><div class="text-purple fw-bold" style="font-size:1.75rem"><?= mgmt_format_bytes($phpMemory) ?></div><div class="text-muted text-xs text-uppercase">PHP Memory</div></div></div></div>
</div>

<?php if (!empty($tables)): ?>
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header fw-bold">Table Size Distribution</div>
            <div class="card-body"><canvas id="chartSizes" height="220"></canvas></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header fw-bold">Rows per Table</div>
            <div class="card-body"><canvas id="chartRows" height="220"></canvas></div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header fw-bold">System Resources</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between text-sm mb-1">
                        <span>PHP Memory (<?= mgmt_format_bytes($phpMemory) ?> / <?= mgmt_format_bytes($phpPeakMemory) ?> peak)</span>
                        <span class="text-muted"><?= ini_get('memory_limit') ?></span>
                    </div>
                    <div class="progress">
                        <?php $memLimit = ini_get('memory_limit'); $memBytes = 0;
                            if ($memLimit && $memLimit !== '-1') {
                                $unit = strtoupper(substr($memLimit, -1));
                                $val = (int)$memLimit;
                                if ($unit === 'G') $memBytes = $val * 1073741824;
                                elseif ($unit === 'M') $memBytes = $val * 1048576;
                                elseif ($unit === 'K') $memBytes = $val * 1024;
                                else $memBytes = $val;
                            }
                            $memPct = $memBytes > 0 ? round(($phpMemory / $memBytes) * 100, 1) : 0;
                        ?>
                        <div class="progress-bar bg-info" style="width:<?= min($memPct, 100) ?>%"><?= min($memPct, 100) ?>%</div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between text-sm mb-1">
                        <span>Disk Usage (<?= mgmt_format_bytes($diskUsed) ?> / <?= mgmt_format_bytes($diskTotal) ?>)</span>
                        <span class="text-muted"><?= $diskPct ?>%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar <?= $diskPct > 80 ? 'bg-danger' : ($diskPct > 60 ? 'bg-warning' : 'bg-success') ?>" style="width:<?= $diskPct ?>%"><?= $diskPct ?>%</div>
                    </div>
                </div>
                <?php if ($loadAvgs): ?>
                <div>
                    <div class="d-flex justify-content-between text-sm mb-1">
                        <span>CPU Load</span>
                        <span class="text-muted">1 min / 5 min / 15 min</span>
                    </div>
                    <div class="d-flex gap-2">
                        <?php foreach ($loadAvgs as $i => $v): ?>
                            <div class="flex-fill text-center p-2 rounded" style="background:#1c2333;">
                                <div class="fw-bold <?= $v > 1 ? 'text-warning' : 'text-success' ?>"><?= number_format($v, 2) ?></div>
                                <div class="text-xs text-muted"><?= [0 => '1m', 1 => '5m', 2 => '15m'][$i] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header fw-bold">Application Overview</div>
            <div class="card-body">
                <div class="row g-2 text-sm">
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#1c2333;">
                            <div class="fw-medium">PHP <?= $phpVersion ?></div>
                            <div class="text-muted text-xs"><?= $phpExtCount ?> extensions loaded</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#1c2333;">
                            <div class="fw-medium"><?= htmlspecialchars($serverSoft) ?></div>
                            <div class="text-muted text-xs">Server software</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#1c2333;">
                            <div class="fw-medium"><?= number_format($controllerCount) ?></div>
                            <div class="text-muted text-xs">Controllers</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#1c2333;">
                            <div class="fw-medium"><?= number_format($modelCount) ?></div>
                            <div class="text-muted text-xs">Models</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#1c2333;">
                            <div class="fw-medium"><?= number_format($middlewareCount) ?></div>
                            <div class="text-muted text-xs">Middleware</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2 rounded" style="background:#1c2333;">
                            <div class="fw-medium"><?= $migratedCount ?> / <?= $migrationCount ?></div>
                            <div class="text-muted text-xs">Migrations (ran/total)</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-dark mb-4">
    <div class="card-header fw-bold">Tables Overview</div>
    <?php if (!empty($tables)): ?>
        <div class="overflow-auto">
            <table class="data-table data-table-bordered data-table-compact mb-0">
                <thead><tr><th>Name</th><th>Engine</th><th>Rows</th><th>Data Size</th><th>Index Size</th><th>Total</th><th>Collation</th><th>Created</th></tr></thead>
                <tbody>
                    <?php foreach ($tables as $t): ?>
                        <?php
                            $ds = (int)($t['Data_length'] ?? 0);
                            $is = (int)($t['Index_length'] ?? 0);
                        ?>
                        <tr>
                            <td><a href="/management/database?tbl=<?= urlencode($t['Name'] ?? '') ?>" class="text-info text-decoration-none fw-medium"><?= htmlspecialchars($t['Name'] ?? '') ?></a></td>
                            <td><?= htmlspecialchars($t['Engine'] ?? '-') ?></td>
                            <td><?= number_format((int)($t['Rows'] ?? 0)) ?></td>
                            <td><?= mgmt_format_bytes($ds) ?></td>
                            <td><?= mgmt_format_bytes($is) ?></td>
                            <td><?= mgmt_format_bytes($ds + $is) ?></td>
                            <td><?= htmlspecialchars($t['Collation'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($t['Create_time'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="card-body text-muted"><?= $db ? 'No tables found.' : 'Database not configured or not connected.' ?></div>
    <?php endif; ?>
</div>

<?php if (!empty($tables)): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.body.classList.contains('theme-dark');
    var gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    var textColor = isDark ? '#c9d1d9' : '#24292f';

    var colors = ['#32de84','#00A8E8','#a855f7','#FFB72C','#ec4899','#06b6d4','#14b8a6','#6366f1','#FF0800','#ff6900'];
    var tableNames = <?= json_encode(array_map(function($t) { return $t['Name'] ?? '?'; }, $tables)) ?>;
    var dataSizes = <?= json_encode(array_map(function($t) { return (int)($t['Data_length'] ?? 0) + (int)($t['Index_length'] ?? 0); }, $tables)) ?>;
    var rowCounts = <?= json_encode(array_map(function($t) { return (int)($t['Rows'] ?? 0); }, $tables)) ?>;

    new Chart(document.getElementById('chartSizes'), {
        type: 'doughnut',
        data: {
            labels: tableNames,
            datasets: [{
                data: dataSizes,
                backgroundColor: colors.slice(0, tableNames.length),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: textColor, font: { size: 11 }, padding: 12, boxWidth: 12 }
                }
            }
        }
    });

    new Chart(document.getElementById('chartRows'), {
        type: 'bar',
        data: {
            labels: tableNames,
            datasets: [{
                label: 'Rows',
                data: rowCounts,
                backgroundColor: colors.slice(0, tableNames.length),
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor }, beginAtZero: true }
            }
        }
    });
});
</script>
<?php endif; ?>
