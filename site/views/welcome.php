<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AuraPHP') ?></title>
    <?php ownstrap_css(); ?>
</head>
<body class="theme-dark">
    <div class="d-flex align-items-center justify-content-center min-h-screen p-4 pb-5">
        <div class="text-center mx-auto my-4" style="max-width: 800px;">
            <div class="mb-5 d-flex justify-content-center gap-2">
                <span class="badge badge-success">v1.0.0</span>
                <span class="badge badge-info">OwnStrap</span>
            </div>

            <h1 class="main-text text-gradient-success mb-4"><?= htmlspecialchars($title) ?></h1>
            <p class="lead mb-5 mx-auto" style="color: #a0a0b8; max-width: 600px;"><?= htmlspecialchars($message) ?> — built with <strong class="text-success">OwnStrap</strong> and <strong class="text-info">AuraCore</strong>.</p>

            <div class="d-flex justify-content-center gap-3 flex-wrap mb-5">
                <a href="<?= site_url('docs/framework') ?>" class="btn btn-success btn-lg">Get Started</a>
                <a href="<?= site_url('components') ?>" class="btn btn-outline-info btn-lg">Components</a>
            </div>

            <div class="row mb-4">
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <div class="card card-dark">
                        <div class="card-body text-center">
                            <div class="text-success fw-bold text-4xl mb-2">🎨</div>
                            <div class="fw-bold mb-1 text-success">Color System</div>
                            <div class="text-muted text-sm">300+ color utilities with gradients, shades, and hover variants.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <div class="card card-dark">
                        <div class="card-body text-center">
                            <div class="text-info fw-bold text-4xl mb-2">⚡</div>
                            <div class="fw-bold mb-1 text-info">Components</div>
                            <div class="text-muted text-sm">Modals, tabs, accordions, dropdowns, toasts, and more.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <div class="card card-dark">
                        <div class="card-body text-center">
                            <div class="text-purple fw-bold text-4xl mb-2">📱</div>
                            <div class="fw-bold mb-1 text-purple">Responsive</div>
                            <div class="text-muted text-sm">Mobile-first grid system with responsive breakpoints.</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-3 mb-3">
                    <div class="card card-dark">
                        <div class="card-body text-center">
                            <div class="text-warning fw-bold text-4xl mb-2">🔗</div>
                            <div class="fw-bold mb-1 text-warning">Routing</div>
                            <div class="text-muted text-sm">Simple yet powerful router with dynamic parameters.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-4 flex-wrap mt-5 pt-4 border-top">
                <div class="text-center">
                    <div class="main-text text-gradient-success text-4xl fw-bolder">10+</div>
                    <div class="text-muted text-sm">Components</div>
                </div>
                <div class="text-center">
                    <div class="main-text text-gradient-info text-4xl fw-bolder">300+</div>
                    <div class="text-muted text-sm">Utilities</div>
                </div>
                <div class="text-center">
                    <div class="main-text text-gradient-purple text-4xl fw-bolder">4</div>
                    <div class="text-muted text-sm">Breakpoints</div>
                </div>
            </div>

            <div class="mt-5 pt-4 d-flex justify-content-center gap-3 flex-wrap border-top">
                <a href="<?= site_url('docs/framework') ?>" class="text-muted text-sm px-3 py-1">Framework Docs</a>
                <a href="<?= site_url('docs/ownstrap') ?>" class="text-muted text-sm px-3 py-1">OwnStrap Docs</a>
                <a href="<?= site_url('components') ?>" class="text-muted text-sm px-3 py-1">Components</a>
                <a href="<?= site_url('colors') ?>" class="text-muted text-sm px-3 py-1">Colors</a>
                <a href="<?= site_url('demo') ?>" class="text-muted text-sm px-3 py-1">Demo</a>
            </div>
        </div>
    </div>
</body>
</html>
