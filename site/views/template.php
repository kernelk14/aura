<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AuraPHP') ?></title>
    <?php ownstrap_css(); ?>
</head>
<body class="theme-light">

<nav class="navbar sticky-top border-bottom backdrop-blur bg-glass">
    <a href="<?= site_url('') ?>" class="navbar-brand">AuraPHP</a>
    <ul class="navbar-nav">
        <li><a href="<?= site_url('docs/framework') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'docs/framework') !== false ? 'active' : '' ?>">Docs</a></li>
        <li><a href="<?= site_url('components') ?>" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/components' ? 'active' : '' ?>">Components</a></li>
        <li><a href="<?= site_url('colors') ?>" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/colors' ? 'active' : '' ?>">Colors</a></li>
        <li><a href="<?= site_url('demo') ?>" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/demo' ? 'active' : '' ?>">Demo</a></li>
        <li><a href="<?= site_url('docs/ownstrap') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'docs/ownstrap') !== false ? 'active' : '' ?>">OwnStrap</a></li>
    </ul>
    <button class="nav-link" onclick="toggleTheme()" id="themeToggle" aria-label="Toggle theme">&#9790;</button>
</nav>
<script>
(function(){var t=localStorage.getItem("theme"),b=document.body;if(t&&t!=="theme-light"){b.classList.replace("theme-light","theme-dark");var i=document.getElementById("themeToggle");i&&(i.innerHTML="&#9788;")}})();function toggleTheme(){var e=document.body,i=document.getElementById("themeToggle");e.classList.toggle("theme-dark"),e.classList.toggle("theme-light");var n=e.classList.contains("theme-dark");localStorage.setItem("theme",n?"theme-dark":"theme-light"),i&&(i.innerHTML=n?"&#9788;":"&#9790;")}
</script>
