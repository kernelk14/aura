<?php
$db = $this->db;
$seedersDir = SITE_PATH . 'seeders';
$seederFiles = [];
if (is_dir($seedersDir)) {
    $files = glob($seedersDir . '/*.php');
    sort($files);
    foreach ($files as $file) { $seederFiles[] = basename($file); }
}
?>

<?php if (!empty($seederFiles)): ?>
    <div class="mb-3">
        <form method="post" action="/management/seeders" onsubmit="return confirm('Run all seeders?');">
            <input type="hidden" name="run_seeder" value="1">
            <button type="submit" class="btn btn-primary btn-sm">Run All Seeders</button>
        </form>
    </div>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['run_seeder'])): ?>
        <?php $this->runSeederAction(); ?>
    <?php endif; ?>
<?php endif; ?>

<div class="card card-dark">
    <div class="card-header fw-bold">Available Seeders</div>
    <?php if (empty($seederFiles)): ?>
        <div class="card-body text-muted">No seeder files found in site/seeders/</div>
    <?php else: ?>
        <div class="overflow-auto">
            <table class="data-table data-table-bordered data-table-compact mb-0">
                <thead><tr><th>File</th></tr></thead>
                <tbody><?php foreach ($seederFiles as $f): ?><tr><td><?= htmlspecialchars($f) ?></td></tr><?php endforeach; ?></tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
