<?php
$logDir = __DIR__ . '/../../storage/logs';
$logFiles = [];
$logContent = '';
$selectedLog = $_GET['log'] ?? '';

if (is_dir($logDir)) {
    $files = glob($logDir . '/*.log');
    rsort($files);
    foreach ($files as $f) { $logFiles[] = basename($f); }
    if (empty($selectedLog) && !empty($logFiles)) $selectedLog = $logFiles[0];
    if ($selectedLog && in_array($selectedLog, $logFiles)) {
        $logContent = htmlspecialchars(file_get_contents($logDir . '/' . $selectedLog));
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_log'])) {
    $clearFile = $_POST['clear_log'];
    $clearPath = $logDir . '/' . basename($clearFile);
    if (file_exists($clearPath)) {
        file_put_contents($clearPath, '');
        echo '<div class="alert alert-warning">Log cleared: ' . htmlspecialchars($clearFile) . '</div>';
    }
}
?>

<div class="row g-3">
    <div class="col-md-3">
        <div class="card card-dark">
            <div class="card-header fw-bold">Log Files</div>
            <?php if (empty($logFiles)): ?>
                <div class="card-body text-muted">No log files found.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($logFiles as $lf): ?>
                        <a href="/management/logs?log=<?= urlencode($lf) ?>"
                           class="list-group-item bg-transparent border-0 <?= $lf === $selectedLog ? 'active' : '' ?>"
                           style="font-size:0.8125rem"><?= htmlspecialchars($lf) ?></a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card card-dark">
            <div class="card-header fw-bold d-flex align-items-center justify-content-between">
                <span>Log: <?= htmlspecialchars($selectedLog ?: '(none)') ?></span>
                <?php if ($selectedLog): ?>
                    <form method="post" action="/management/logs" style="display:inline" onsubmit="return confirm('Clear this log file?');">
                        <input type="hidden" name="clear_log" value="<?= htmlspecialchars($selectedLog) ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Clear</button>
                    </form>
                <?php endif; ?>
            </div>
            <?php if ($logContent): ?>
                <pre class="p-3 mb-0 mgmt-code" style="max-height:60vh;overflow:auto;white-space:pre-wrap;word-break:break-all;"><?= $logContent ?></pre>
            <?php else: ?>
                <div class="card-body text-muted">Log is empty or not found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>
