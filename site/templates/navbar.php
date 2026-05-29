<nav class="navbar sticky-top border-bottom backdrop-blur bg-glass">
    <a href="<?= site_url('') ?>" class="navbar-brand">AuraPHP</a>
    <ul class="navbar-nav">
        <li><a href="<?= site_url('docs/framework') ?>" class="nav-link <?= strpos($_SERVER['REQUEST_URI'] ?? '', 'docs/framework') !== false ? 'active' : '' ?>">Docs</a></li>
    </ul>
    <button class="nav-link" onclick="toggleTheme()" id="themeToggle" aria-label="Toggle theme">&#9790;</button>
</nav>