<?php
$db = $this->db;
$action = $_GET['action'] ?? 'tables';
$selectedTable = $_GET['tbl'] ?? '';
?>
<?php if (!$selectedTable): ?>
<div class="tabs mb-4">
    <button class="tab-button <?= $action === 'tables' ? 'active' : '' ?>" onclick="window.location.href='/management/database'">Tables</button>
    <button class="tab-button <?= $action === 'query' ? 'active' : '' ?>" onclick="window.location.href='/management/database?action=query'">SQL Query</button>
</div>
<?php endif; ?>

<?php if ($action === 'query'): ?>

    <div class="card card-dark">
        <div class="card-header fw-bold">Run SQL Query</div>
        <div class="card-body">
            <form method="post" action="/management/database?action=query" id="sqlForm">
                <div class="mb-3">
                    <textarea name="sql" rows="8" class="input-control font-monospace" placeholder="SELECT * FROM ..." style="tab-size:4;"><?= htmlspecialchars($_POST['sql'] ?? '') ?></textarea>
                </div>
                <div class="d-flex align-items-center gap-2 mb-3">
                    <button type="submit" class="btn btn-primary">Execute</button>
                    <span class="text-muted text-xs">Ctrl+Enter or Cmd+Enter to run &middot; SELECT, SHOW, DESCRIBE, INSERT, UPDATE, DELETE</span>
                </div>
            </form>
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sql'])): ?>
                <hr class="border-secondary">
                <?php $this->handleQuery(); ?>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($selectedTable): ?>

    <?php
        $pdo = $db ? $db->getPdo() : null;
        $columns = []; $rows = [];
        if ($pdo) {
            try {
                $stmt = $pdo->query("DESCRIBE `" . str_replace('`', '``', $selectedTable) . "`");
                $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $stmt = $pdo->query("SELECT COUNT(*) FROM `" . str_replace('`', '``', $selectedTable) . "`");
                $totalCount = (int)$stmt->fetchColumn();
                $stmt = $pdo->query("SELECT * FROM `" . str_replace('`', '``', $selectedTable) . "` LIMIT 100");
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            } catch (\Exception $e) {
                echo '<div class="alert alert-danger">' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    ?>
    <div class="d-flex align-items-center gap-3 mb-4">
        <a href="/management/database" class="btn btn-sm btn-secondary">&larr; Tables</a>
        <h4 class="fw-bold mb-0"><?= htmlspecialchars($selectedTable) ?></h4>
        <?php if (isset($totalCount)): ?>
            <span class="badge bg-info"><?= number_format($totalCount) ?> rows</span>
        <?php endif; ?>
    </div>
    <div class="row g-3">
        <div class="col-md-5">
            <div class="card card-dark">
                <div class="card-header fw-bold">Columns</div>
                <?php if (!empty($columns)): ?>
                    <div class="overflow-auto" style="max-height:400px;">
                        <table class="data-table data-table-bordered data-table-compact mb-0">
                            <thead><tr><?php foreach (array_keys($columns[0]) as $col): ?><th><?= htmlspecialchars($col) ?></th><?php endforeach; ?></tr></thead>
                            <tbody><?php foreach ($columns as $col): ?><tr><?php foreach ($col as $val): ?><td><?= htmlspecialchars((string)$val) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card card-dark">
                <div class="card-header fw-bold">Data <span class="text-muted fw-normal">(first 100)</span></div>
                <?php if (!empty($rows)): ?>
                    <div class="overflow-auto" style="max-height:400px;">
                        <table class="data-table data-table-bordered data-table-compact mb-0">
                            <thead><tr><?php foreach (array_keys($rows[0]) as $col): ?><th><?= htmlspecialchars($col) ?></th><?php endforeach; ?></tr></thead>
                            <tbody><?php foreach ($rows as $row): ?><tr><?php foreach ($row as $val): ?><td><?= htmlspecialchars((string)$val) ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="card-body text-muted">No data</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php else: ?>

    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
        <div style="position:relative;flex:1;max-width:320px;">
            <input type="text" id="tableFilter" class="input-control" placeholder="Filter tables..." oninput="filterTables(this.value)">
        </div>
        <?php
            $totalTableCount = 0;
            if ($db) {
                try {
                    $stmt = $pdo->query("SHOW TABLE STATUS");
                    $tables = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    $totalTableCount = count($tables);
                } catch (\Exception $e) { $tables = []; }
            } else {
                $tables = [];
            }
        ?>
        <span class="text-muted text-xs"><?= $totalTableCount ?> table(s)</span>
    </div>

    <div class="card card-dark">
        <div class="card-header fw-bold d-flex align-items-center justify-content-between">
            <span>Database Tables</span>
            <?php if ($db && !empty($tables)): ?>
                <span class="text-muted text-xs">Click a table name to browse</span>
            <?php endif; ?>
        </div>
        <?php if (!$db): ?>
            <div class="card-body text-warning">Database not configured or not connected.</div>
        <?php elseif (empty($tables)): ?>
            <div class="card-body text-muted">No tables found in the database.</div>
        <?php else: ?>
            <div class="overflow-auto">
                <table class="data-table data-table-bordered data-table-compact mb-0" id="tablesTable">
                    <thead><tr><th>Name</th><th>Engine</th><th>Rows</th><th>Size</th><th>Collation</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($tables as $t): ?>
                            <?php
                                $size = ($t['Data_length'] ?? 0) + ($t['Index_length'] ?? 0);
                                $sizeFormatted = $size > 1048576 ? round($size / 1048576, 1) . ' MiB' : ($size > 1024 ? round($size / 1024, 1) . ' KiB' : $size . ' B');
                            ?>
                            <tr class="table-row">
                                <td class="fw-medium">
                                    <a href="/management/database?tbl=<?= urlencode($t['Name'] ?? '') ?>" class="text-info text-decoration-none"><?= htmlspecialchars($t['Name'] ?? '') ?></a>
                                </td>
                                <td><?= htmlspecialchars($t['Engine'] ?? '-') ?></td>
                                <td><?= number_format((int)($t['Rows'] ?? 0)) ?></td>
                                <td><?= $sizeFormatted ?></td>
                                <td><?= htmlspecialchars($t['Collation'] ?? '-') ?></td>
                                <td class="text-end">
                                    <a href="/management/database?tbl=<?= urlencode($t['Name'] ?? '') ?>" class="btn btn-sm btn-info">Browse</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script>
    function filterTables(val) {
        var q = val.toLowerCase();
        document.querySelectorAll('#tablesTable .table-row').forEach(function(tr) {
            tr.style.display = tr.textContent.toLowerCase().indexOf(q) > -1 ? '' : 'none';
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('sqlForm');
        if (form) {
            form.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    form.submit();
                }
            });
        }
    });
    </script>

<?php endif; ?>
