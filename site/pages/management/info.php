<?php
ob_start();
phpinfo();
$phpinfo = ob_get_clean();
$phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
$phpinfo = str_replace('class="e"', 'style="font-weight:600;color:#58a6ff;white-space:nowrap;"', $phpinfo);
$phpinfo = str_replace('class="v"', 'style="word-break:break-all;"', $phpinfo);
$phpinfo = str_replace('class="h"', 'style="background:#1c2333;padding:0.5rem 1rem;font-weight:700;font-size:1rem;border-bottom:1px solid #30363d;"', $phpinfo);
$phpinfo = str_replace('<table', '<table class="data-table data-table-bordered data-table-compact mb-0"', $phpinfo);
$phpinfo = str_replace('<tr>', '<tr style="border-bottom:1px solid #21262d;">', $phpinfo);
$phpinfo = str_replace('<td', '<td style="padding:0.375rem 1rem;"', $phpinfo);
?>

<div class="card card-dark">
    <div class="card-header fw-bold">PHP Configuration</div>
    <div class="overflow-auto" style="max-height:70vh;"><?= $phpinfo ?></div>
</div>
