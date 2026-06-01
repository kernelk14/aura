<?php
$routesFile = SYSTEM_PATH . 'config/routes.php';
$routes = [];
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    preg_match_all('/\$(router|app)->(get|post|put|patch|delete|match|any|resource)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $routes[] = ['method' => strtoupper($m[2]), 'uri' => $m[3]];
    }
}
?>

<div class="card card-dark">
    <div class="card-header fw-bold">Registered Routes</div>
    <?php if (empty($routes)): ?>
        <div class="card-body text-muted">No routes detected in routes.php.</div>
    <?php else: ?>
        <div class="overflow-auto">
            <table class="data-table data-table-bordered data-table-compact mb-0">
                <thead><tr><th>Method</th><th>URI</th></tr></thead>
                <tbody>
                    <?php foreach ($routes as $r): ?>
                        <tr>
                            <td><span class="badge bg-<?= $r['method'] === 'GET' ? 'success' : ($r['method'] === 'POST' ? 'primary' : 'secondary') ?>"><?= $r['method'] ?></span></td>
                            <td><code><?= htmlspecialchars($r['uri']) ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
