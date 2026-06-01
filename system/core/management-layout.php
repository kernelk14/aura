<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> | Management</title>
    <?php ownstrap_css(); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <style>
        .mgmt-layout { display: flex; min-height: 100vh; }
        .mgmt-sidebar {
            width: 230px; flex-shrink: 0;
            display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 100;
            overflow-y: auto;
        }
        .mgmt-main {
            margin-left: 230px; flex: 1; min-width: 0;
            padding: 1.5rem 2rem 3rem;
        }
        .mgmt-code { font-family: 'SF Mono', 'Fira Code', monospace; font-size: 0.75rem; line-height: 1.5; }
        .data-table-compact th,
        .data-table-compact td { padding: 0.35rem 0.5rem; font-size: 0.8125rem; }
        .data-table-compact th { font-size: 0.75rem; }
        .mgmt-main .progress-bar {
            font-size: 0.6rem;
            line-height: 0.75rem;
            text-align: center;
            font-weight: 700;
        }

        .sidebar-link {
            display: flex; align-items: center; gap: 0.625rem;
            padding: 0.5rem 1.25rem;
            font-size: 0.8125rem;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: all 0.15s ease;
        }
        .sidebar-link:hover { border-left-color: #58a6ff44; }
        .sidebar-link.active,
        .sidebar-link.active:hover { border-left-color: #58a6ff; }

        .theme-dark .sidebar-link { color: #c9d1d9; }
        .theme-dark .sidebar-link:hover { background: #161b22; }
        .theme-dark .sidebar-link.active { background: #1f6feb15; color: #58a6ff; }
        .theme-light .sidebar-link { color: #24292f; }
        .theme-light .sidebar-link:hover { background: #eaeef2; }
        .theme-light .sidebar-link.active { background: #ddf4ff; color: #0969da; border-left-color: #0969da; }

        .theme-light .card.card-dark {
            background: #ffffff;
            border-color: #d0d7de;
        }
        .theme-light .card.card-dark .card-header {
            background: #f6f8fa;
            border-bottom-color: #d0d7de;
        }
        .theme-light .card.card-dark .list-group-item.active {
            background: #ddf4ff;
            border-color: #d0d7de;
            color: #0969da;
        }

        @media (max-width: 768px) {
            .mgmt-sidebar { display: none; }
            .mgmt-main { margin-left: 0; padding: 1rem; }
            .mgmt-sidebar.mobile-open { display: flex; width: 100%; }
        }
    </style>
</head>
<body class="theme-dark">
<div class="mgmt-layout">

    <!-- Sidebar -->
    <aside class="mgmt-sidebar bg-subtle">
        <div class="px-3 py-3 border-bottom d-flex align-items-center gap-2">
            <span style="font-size:1.25rem;">&#9733;</span>
            <div>
                <div class="fw-bold" style="font-size:0.9375rem;">AuraPHP</div>
                <div class="text-xs text-muted" style="margin-top:-1px;">Management Panel</div>
            </div>
        </div>

        <nav class="py-2 flex-grow-1">
            <?php
                $sections = [];
                foreach ($pages as $id => $info) {
                    $sections[$info['section']][] = ['id' => $id, 'title' => $info['title'], 'icon' => $info['icon']];
                }
            ?>
            <?php foreach ($sections as $sectionName => $sectionPages): ?>
                <div class="px-3 pt-3 pb-1 text-xs text-uppercase fw-bold text-muted" style="letter-spacing:0.5px;"><?= htmlspecialchars($sectionName) ?></div>
                <?php foreach ($sectionPages as $p): ?>
                    <a href="/management/<?= $p['id'] ?>"
                       class="sidebar-link <?= $p['id'] === $page ? 'active' : '' ?>">
                        <span style="width:1.25rem;text-align:center;font-size:0.75rem;opacity:0.65;"><?= $p['icon'] ?></span>
                        <?= htmlspecialchars($p['title']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </nav>

        <div class="px-3 py-3 border-top d-flex align-items-center justify-content-between">
            <a href="/" class="text-muted text-decoration-none d-flex align-items-center gap-1" style="font-size:0.75rem;">&larr; Back</a>
            <button class="btn btn-sm btn-secondary" onclick="toggleMgmtTheme()" title="Toggle theme" style="font-size:0.6875rem;">&#9788; <span id="mgmt-theme-val">Dark</span></button>
        </div>
    </aside>

    <!-- Main -->
    <main class="mgmt-main">
        <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
            <div>
                <div class="text-muted text-xs">Management / <?= htmlspecialchars($title) ?></div>
                <h2 class="fw-bold mb-0"><?= htmlspecialchars($title) ?></h2>
            </div>
        </div>

        <?= $content ?>
    </main>
</div>

<script>
function toggleMgmtTheme() {
    var b = document.body;
    b.classList.toggle('theme-dark');
    b.classList.toggle('theme-light');
    var val = b.classList.contains('theme-dark') ? 'Dark' : 'Light';
    document.getElementById('mgmt-theme-val').textContent = val;
    localStorage.setItem('mgmt-theme', val);
}
(function() {
    var saved = localStorage.getItem('mgmt-theme');
    if (saved === 'Light') {
        document.body.classList.remove('theme-dark');
        document.body.classList.add('theme-light');
        document.getElementById('mgmt-theme-val').textContent = 'Light';
    }
})();
</script>
<?php ownstrap_js(); ?>
</body>
</html>
