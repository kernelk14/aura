<?php
$envFile = __DIR__ . '/../../.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        [$key, $val] = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        $displayVal = strlen($val) > 60 ? substr($val, 0, 60) . '...' : $val;
        $envVars[$key] = $displayVal;
    }
}

$serverEnv = $_ENV;
?>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header fw-bold">.env File</div>
            <?php if (empty($envVars)): ?>
                <div class="card-body text-muted">No .env file found or empty.</div>
            <?php else: ?>
                <div class="overflow-auto">
                    <table class="data-table data-table-bordered data-table-compact mb-0">
                        <thead><tr><th>Key</th><th>Value</th></tr></thead>
                        <tbody>
                            <?php foreach ($envVars as $key => $val): ?>
                                <tr><td><code><?= htmlspecialchars($key) ?></code></td><td><code><?= htmlspecialchars($val) ?></code></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-dark">
            <div class="card-header fw-bold">$_ENV <span class="text-muted fw-normal">(runtime)</span></div>
            <?php if (empty($serverEnv)): ?>
                <div class="card-body text-muted">No environment variables.</div>
            <?php else: ?>
                <div class="overflow-auto" style="max-height:50vh;">
                    <table class="data-table data-table-bordered data-table-compact mb-0">
                        <thead><tr><th>Key</th><th>Value</th></tr></thead>
                        <tbody>
                            <?php foreach ($serverEnv as $key => $val): ?>
                                <tr><td><code><?= htmlspecialchars($key) ?></code></td><td><code><?= htmlspecialchars(is_string($val) ? (strlen($val) > 80 ? substr($val, 0, 80) . '...' : $val) : json_encode($val)) ?></code></td></tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
