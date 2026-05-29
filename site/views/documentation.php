<?php include 'template.php'; ?>

<div class="d-flex">
    <?php template('sidebar-ownstrap'); ?>

    <main class="flex-grow-1 ps-4 pb-4 ms-2" style="min-width:0;max-width:1400px;">
        <h1 class="fw-bolder text-4xl mt-3 mb-1">OwnStrap CSS/JS</h1>
        <p class="lead mb-4">Complete reference for OwnStrap — a lightweight CSS/JS framework for AuraPHP.</p>

        <!-- ========== GETTING STARTED ========== -->
        <section id="getting-started">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Getting Started</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Include OwnStrap in your view templates using the helper functions:</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <code class="fw-bold">&#60;?php ownstrap_css(); ?&#62;</code>
                                    <p class="mt-2 mb-0 text-sm">Loads the main stylesheet (all components, utilities, grid, accessibility).</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <code class="fw-bold">&#60;?php ownstrap_js(); ?&#62;</code>
                                    <p class="mt-2 mb-0 text-sm">Loads the JavaScript library (modals, tabs, accordions, toasts, carousel, etc.).</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <p class="mt-3">The <code>&#60;?php include 'template.php'; ?&#62;</code> include automatically pulls in the HTML shell. Your view content goes between the include and the <code>ownstrap_js()</code> + <code>&lt;/body&gt;&lt;/html&gt;</code> closing tags.</p>
                </div>
            </div>
        </section>

        <!-- ========== THEMES ========== -->
        <section id="themes">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Themes</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Apply a theme to the <code>&lt;body&gt;</code> or any container:</p>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="bg-dark text-white p-4 rounded border">
                                <code class="text-success">.theme-dark</code>
                                <p class="mt-2 mb-0 text-sm">Background: <code>#181818</code>, Text: <code>#FFFFF0</code></p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="bg-light p-4 rounded border">
                                <code>.theme-light</code>
                                <p class="mt-2 mb-0 text-sm">Background: <code>#FFFFF0</code>, Text: <code>#181618</code></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== TYPOGRAPHY ========== -->
        <section id="typography">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Typography</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Headings</h5>
                            <h1 class="mb-1">h1 heading</h1>
                            <h2 class="mb-1">h2 heading</h2>
                            <h3 class="mb-1">h3 heading</h3>
                            <h4 class="mb-1">h4 heading</h4>
                            <h5 class="mb-1">h5 heading</h5>
                            <h5 class="mb-1">h6 heading</h5>
                        </div>
                        <div class="col-md-6">
                            <h5>Font Sizes</h5>
                            <p class="text-xs">.text-xs (0.75rem)</p>
                            <p class="text-sm">.text-sm (0.875rem)</p>
                            <p class="text-base">.text-base (1rem)</p>
                            <p class="text-lg">.text-lg (1.125rem)</p>
                            <p class="text-xl">.text-xl (1.25rem)</p>
                            <p class="text-2xl">.text-2xl (1.5rem)</p>
                            <p class="text-3xl">.text-3xl (1.875rem)</p>
                            <p class="text-4xl">.text-4xl (2.25rem)</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <h5>Font Weights</h5>
                            <p class="fw-light">.fw-light (300)</p>
                            <p class="fw-normal">.fw-normal (400)</p>
                            <p class="fw-semibold">.fw-semibold (600)</p>
                            <p class="fw-bold">.fw-bold (700)</p>
                            <p class="fw-bolder">.fw-bolder (900)</p>
                        </div>
                        <div class="col-md-4">
                            <h5>Text Alignment</h5>
                            <p class="text-start">.text-start</p>
                            <p class="text-center">.text-center</p>
                            <p class="text-end">.text-end</p>
                        </div>
                        <div class="col-md-4">
                            <h5>Text Utilities</h5>
                            <p><span class="text-decoration-underline">.text-decoration-underline</span></p>
                            <p><span class="text-decoration-none">.text-decoration-none</span></p>
                            <p class="text-lowercase">TEXT LOWERCASE</p>
                            <p class="text-uppercase">text uppercase</p>
                            <p class="text-capitalize">text capitalize</p>
                            <p class="font-monospace">.font-monospace</p>
                            <p class="text-truncate" style="max-width: 200px;">This text is truncated with ellipsis</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5>Helpers</h5>
                            <p><code>.lead</code> — larger paragraph text (like this: <span class="lead">lead paragraph</span>)</p>
                            <p><code>.small</code> — smaller text: <span class="small">small text</span></p>
                            <p><code>.code</code> — inline code</p>
                            <p><code>.lh-1</code> — line-height: 1</p>
                            <p><code>.text-nowrap</code> — prevent text wrapping</p>
                            <p><code>.text-break</code> — break long words</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Text Colors</h5>
                            <p class="text-success">.text-success</p>
                            <p class="text-warning">.text-warning</p>
                            <p class="text-danger">.text-danger</p>
                            <p class="text-info">.text-info</p>
                            <p class="text-purple">.text-purple</p>
                            <p class="text-pink">.text-pink</p>
                            <p class="text-cyan">.text-cyan</p>
                            <p class="text-indigo">.text-indigo</p>
                            <p class="text-teal">.text-teal</p>
                            <p class="text-white bg-dark px-1 d-inline-block">.text-white</p>
                            <p><span class="text-dark">.text-dark</span></p>
                            <p class="text-muted">.text-muted</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== GRID ========== -->
        <section id="grid">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Grid System</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>12-column flexbox grid with responsive breakpoints. Wrap columns in <code>.row</code>.</p>
                    <div class="row mb-3">
                        <div class="col-12 col-md-6 col-lg-3 mb-2">
                            <div class="bg-info text-white p-3 rounded text-center text-sm">.col-12 .col-md-6 .col-lg-3</div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-2">
                            <div class="bg-success text-white p-3 rounded text-center text-sm">.col-12 .col-md-6 .col-lg-3</div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-2">
                            <div class="bg-purple text-white p-3 rounded text-center text-sm">.col-12 .col-md-6 .col-lg-3</div>
                        </div>
                        <div class="col-12 col-md-6 col-lg-3 mb-2">
                            <div class="bg-warning text-dark p-3 rounded text-center text-sm">.col-12 .col-md-6 .col-lg-3</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Column Classes</h5>
                            <p><code>.col-1</code> through <code>.col-12</code> — fixed width columns</p>
                            <p><code>.col</code> — equal-width flex column</p>
                            <p><code>.col-auto</code> — auto-width column</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Responsive Breakpoints</h5>
                            <p><code>.col-sm-*</code> — ≥576px</p>
                            <p><code>.col-md-*</code> — ≥768px</p>
                            <p><code>.col-lg-*</code> — ≥992px</p>
                            <p><code>.col-xl-*</code> — ≥1200px</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5>Containers</h5>
                            <p><code>.container</code> — max 1200px, centered</p>
                            <p><code>.container-fluid</code> — full width</p>
                            <p><code>.container-sm/md/lg/xl</code> — max-width variants</p>
                        </div>
                        <div class="col-md-6">
                            <h5>Flex Helpers</h5>
                            <p><code>.d-flex</code>, <code>.flex-column</code>, <code>.flex-wrap</code></p>
                            <p><code>.align-items-center</code></p>
                            <p><code>.justify-content-start/center/end/between/around</code></p>
                            <p><code>.gap-1</code> through <code>.gap-4</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== COMPONENTS ========== -->
        <section id="components">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Components</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <!-- Cards -->
                    <h5 class="border-bottom pb-2 mb-3">Cards</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Card Title</h5>
                                    <p class="card-text">Basic card with .card, .card-title, .card-text</p>
                                    <button class="btn btn-primary">Button</button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-header bg-success text-white">Card Header</div>
                                <div class="card-body">
                                    <p class="card-text">Card with header.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card card-dark bg-dark text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Dark Card</h5>
                                    <p class="card-text">Use <code>.card-dark</code> on dark themes.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <h5 class="border-bottom pb-2 mb-3">Alerts</h5>
                    <div class="alert alert-success">Success alert — <code>.alert .alert-success</code></div>
                    <div class="alert alert-warning">Warning alert — <code>.alert .alert-warning</code></div>
                    <div class="alert alert-danger">Danger alert — <code>.alert .alert-danger</code></div>
                    <div class="alert alert-info">Info alert — <code>.alert .alert-info</code></div>

                    <!-- Badges -->
                    <h5 class="border-bottom pb-2 mb-3">Badges</h5>
                    <span class="badge badge-success">Success</span>
                    <span class="badge badge-warning">Warning</span>
                    <span class="badge badge-danger">Danger</span>
                    <span class="badge badge-info">Info</span>
                    <span class="badge badge-dark">Dark</span>
                    <span class="badge badge-light">Light</span>
                    <span class="badge badge-success rounded-pill">Pill</span>
                    <span class="badge badge-info rounded-pill">Info Pill</span>

                    <!-- Buttons -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Buttons</h5>
                    <p class="mb-2">Solid:</p>
                    <div class="mb-3">
                        <button class="btn btn-primary">Primary</button>
                        <button class="btn btn-secondary">Secondary</button>
                        <button class="btn btn-success">Success</button>
                        <button class="btn btn-warning">Warning</button>
                        <button class="btn btn-danger">Danger</button>
                        <button class="btn btn-info">Info</button>
                        <button class="btn btn-dark">Dark</button>
                        <button class="btn btn-light">Light</button>
                    </div>
                    <p class="mb-2">Outline:</p>
                    <div class="mb-3">
                        <button class="btn btn-outline-primary">Primary</button>
                        <button class="btn btn-outline-secondary">Secondary</button>
                        <button class="btn btn-outline-success">Success</button>
                        <button class="btn btn-outline-warning">Warning</button>
                        <button class="btn btn-outline-danger">Danger</button>
                        <button class="btn btn-outline-info">Info</button>
                        <button class="btn btn-outline-dark">Dark</button>
                    </div>
                    <p class="mb-2">Sizes & Groups:</p>
                    <div class="mb-2">
                        <button class="btn btn-success btn-sm">Small</button>
                        <button class="btn btn-success">Default</button>
                        <button class="btn btn-success btn-lg">Large</button>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="btn-group">
                            <button class="btn btn-info">Left</button>
                            <button class="btn btn-info">Center</button>
                            <button class="btn btn-info">Right</button>
                        </div>
                        <div class="btn-group-vertical">
                            <button class="btn btn-success">Top</button>
                            <button class="btn btn-success">Middle</button>
                            <button class="btn btn-success">Bottom</button>
                        </div>
                    </div>

                    <!-- Progress -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Progress Bars</h5>
                    <div class="progress mb-2"><div class="progress-bar" style="width: 25%;">25%</div></div>
                    <div class="progress mb-2"><div class="progress-bar bg-success" style="width: 50%;">50%</div></div>
                    <div class="progress mb-2"><div class="progress-bar bg-info progress-bar-striped" style="width: 75%;">75% Striped</div></div>
                    <div class="progress mb-2"><div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" style="width: 60%;">60% Animated</div></div>

                    <!-- Spinners -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Spinners</h5>
                    <div class="spinner me-2"></div>
                    <div class="spinner spinner-sm me-2"></div>
                    <div class="spinner-grow me-2"></div>
                    <div class="spinner spinner-success me-2"></div>
                    <div class="spinner spinner-info me-2"></div>

                    <!-- List Groups -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">List Groups</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <ul class="list-group">
                                <li class="list-group-item active">Active</li>
                                <li class="list-group-item">Item 2</li>
                                <li class="list-group-item">Item 3</li>
                                <li class="list-group-item disabled">Disabled</li>
                            </ul>
                        </div>
                        <div class="col-md-4 mb-3">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">Flush Item 1</li>
                                <li class="list-group-item">Flush Item 2</li>
                                <li class="list-group-item">Flush Item 3</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Modals -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Modals</h5>
                    <button class="btn btn-info" onclick="OS.openModal('docModal')">Open Modal</button>
                    <p class="mt-2 text-sm text-muted">Modals use focus trap, Escape to close, click outside to close.</p>

                    <!-- Tabs -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Tabs</h5>
                    <div data-tabs="docTabs">
                        <div class="tabs">
                            <button class="tab-button active" data-tab="docTab1">Tab 1</button>
                            <button class="tab-button" data-tab="docTab2">Tab 2</button>
                            <button class="tab-button" data-tab="docTab3">Tab 3</button>
                        </div>
                        <div id="docTab1" class="tab-content active p-3 border rounded mt-2">Tab 1 content — use arrow keys to navigate.</div>
                        <div id="docTab2" class="tab-content p-3 border rounded mt-2">Tab 2 content.</div>
                        <div id="docTab3" class="tab-content p-3 border rounded mt-2">Tab 3 content.</div>
                    </div>

                    <!-- Accordions -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Accordions</h5>
                    <div class="accordion" data-single>
                        <div class="accordion-item" id="docAcc1">
                            <div class="accordion-header" role="button" tabindex="0">
                                <span>Accordion Item 1</span>
                                <span class="accordion-icon">&#9660;</span>
                            </div>
                            <div class="accordion-body">Body content for item 1.</div>
                        </div>
                        <div class="accordion-item" id="docAcc2">
                            <div class="accordion-header" role="button" tabindex="0">
                                <span>Accordion Item 2</span>
                                <span class="accordion-icon">&#9660;</span>
                            </div>
                            <div class="accordion-body">Body content for item 2.</div>
                        </div>
                    </div>

                    <!-- Dropdowns -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Dropdowns</h5>
                    <div class="dropdown" id="docDropdown">
                        <button class="dropdown-toggle" aria-haspopup="true" aria-expanded="false">Dropdown <span>&#9660;</span></button>
                        <div class="dropdown-menu">
                            <button class="dropdown-item">Action</button>
                            <button class="dropdown-item">Another action</button>
                            <div class="dropdown-divider"></div>
                            <button class="dropdown-item">Separated action</button>
                        </div>
                    </div>

                    <!-- Toasts -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Toasts</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <button class="btn btn-success btn-sm" onclick="OS.success('Success toast')">Success</button>
                        <button class="btn btn-warning btn-sm" onclick="OS.warning('Warning toast')">Warning</button>
                        <button class="btn btn-danger btn-sm" onclick="OS.error('Error toast')">Error</button>
                        <button class="btn btn-info btn-sm" onclick="OS.info('Info toast')">Info</button>
                        <button class="btn btn-purple btn-sm" onclick="OS.purple('Purple toast')">Purple</button>
                        <button class="btn btn-pink btn-sm" onclick="OS.pink('Pink toast')">Pink</button>
                        <button class="btn btn-cyan btn-sm" onclick="OS.cyan('Cyan toast')">Cyan</button>
                        <button class="btn btn-indigo btn-sm" onclick="OS.indigo('Indigo toast')">Indigo</button>
                        <button class="btn btn-teal btn-sm" onclick="OS.teal('Teal toast')">Teal</button>
                    </div>

                    <!-- Collapse -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Collapse</h5>
                    <button class="btn btn-info btn-sm" data-toggle="collapse" data-target="docCollapse">Toggle Collapse</button>
                    <div id="docCollapse" class="collapse mt-2">
                        <div class="card"><div class="card-body">Collapsible content using <code>data-toggle="collapse" data-target="id"</code>.</div></div>
                    </div>

                    <!-- Carousel -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Carousel</h5>
                    <div id="docCarousel" class="carousel" data-carousel="docCarousel" data-interval="5000" style="max-width: 500px; border-radius: 8px; overflow: hidden;">
                        <div class="carousel-inner">
                            <div class="carousel-item active" style="background: #00A8E8; padding: 3rem 2rem; text-align: center;"><h4 class="text-white">Slide 1</h4><p class="text-white">Auto-plays, pauses on hover</p></div>
                            <div class="carousel-item" style="background: #32de84; padding: 3rem 2rem; text-align: center;"><h4 class="text-white">Slide 2</h4></div>
                            <div class="carousel-item" style="background: #a855f7; padding: 3rem 2rem; text-align: center;"><h4 class="text-white">Slide 3</h4></div>
                        </div>
                        <button class="carousel-control-prev" onclick="OS.prevCarousel('docCarousel')">&lsaquo;</button>
                        <button class="carousel-control-next" onclick="OS.nextCarousel('docCarousel')">&rsaquo;</button>
                        <div class="carousel-indicators">
                            <button class="carousel-indicator active" onclick="OS.showCarouselItem('docCarousel', 0)"></button>
                            <button class="carousel-indicator" onclick="OS.showCarouselItem('docCarousel', 1)"></button>
                            <button class="carousel-indicator" onclick="OS.showCarouselItem('docCarousel', 2)"></button>
                        </div>
                    </div>

                    <!-- Tooltips -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Tooltips</h5>
                    <button class="btn btn-primary btn-sm" data-tooltip="Tooltip on top">Hover me</button>
                    <button class="btn btn-success btn-sm" data-tooltip="Another tooltip">Another</button>
                    <p class="mt-2 text-sm text-muted">Add <code>data-tooltip="text"</code> to any element.</p>

                    <!-- Pagination -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Pagination</h5>
                    <div class="pagination">
                        <span class="page-link disabled">&laquo;</span>
                        <span class="page-link active">1</span>
                        <span class="page-link">2</span>
                        <span class="page-link">3</span>
                        <span class="page-link">&raquo;</span>
                    </div>

                    <!-- Breadcrumbs -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Breadcrumbs</h5>
                    <div class="breadcrumb">
                        <span class="breadcrumb-item"><a href="#">Home</a></span>
                        <span class="breadcrumb-item"><a href="#">Library</a></span>
                        <span class="breadcrumb-item active">Data</span>
                    </div>

                    <!-- Navbar -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Navbar / Nav</h5>
                    <nav class="navbar mb-2 bg-light rounded border">
                        <span class="navbar-brand">Brand</span>
                        <ul class="navbar-nav">
                            <li><a href="#" class="nav-link active">Home</a></li>
                            <li><a href="#" class="nav-link">Link</a></li>
                            <li><a href="#" class="nav-link disabled">Disabled</a></li>
                        </ul>
                    </nav>

                    <!-- Input Groups -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Input Groups</h5>
                    <div class="input-group" style="max-width: 400px;">
                        <span class="input-group-text">@</span>
                        <input type="text" class="input-control" placeholder="Username">
                        <button class="btn btn-info">Check</button>
                    </div>

                    <!-- Shadows -->
                    <h5 class="border-bottom pb-2 mb-3 mt-4">Shadows</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="shadow-sm p-4 rounded bg-light text-center" style="width: 120px;">.shadow-sm</div>
                        <div class="shadow p-4 rounded bg-light text-center" style="width: 120px;">.shadow</div>
                        <div class="shadow-lg p-4 rounded bg-light text-center" style="width: 120px;">.shadow-lg</div>
                        <div class="shadow-none p-4 rounded bg-light text-center" style="width: 120px;">.shadow-none</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== FORMS ========== -->
        <section id="forms">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Forms</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Form Controls</h5>
                            <div class="field-group mb-3">
                                <label class="field-label">Text Input</label>
                                <input type="text" class="input-control" placeholder=".input-control">
                            </div>
                            <div class="field-group mb-3">
                                <label class="field-label">Email</label>
                                <input type="email" class="input-control" placeholder="email@example.com">
                            </div>
                            <div class="field-group mb-3">
                                <label class="field-label">Select</label>
                                <select class="select-control">
                                    <option>.select-control</option>
                                    <option>Option 1</option>
                                </select>
                            </div>
                            <div class="field-group mb-3">
                                <label class="field-label">Textarea</label>
                                <textarea class="input-control" rows="3" placeholder="textarea.input-control"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5>Sizing</h5>
                            <div class="field-group mb-3">
                                <label class="field-label">Small</label>
                                <input type="text" class="input-control input-control-sm" placeholder=".input-control-sm">
                            </div>
                            <div class="field-group mb-3">
                                <label class="field-label">Default</label>
                                <input type="text" class="input-control" placeholder=".input-control">
                            </div>
                            <div class="field-group mb-3">
                                <label class="field-label">Large</label>
                                <input type="text" class="input-control input-control-lg" placeholder=".input-control-lg">
                            </div>
                            <h5 class="mt-4">Checkboxes & Radios</h5>
                            <div class="check-option">
                                <input class="check-input" type="checkbox" id="docCheck1" checked>
                                <label class="check-label" for="docCheck1">Checked</label>
                            </div>
                            <div class="check-option">
                                <input class="check-input" type="checkbox" id="docCheck2">
                                <label class="check-label" for="docCheck2">Unchecked</label>
                            </div>
                            <div class="check-option">
                                <input class="check-input" type="radio" name="docRadio" id="docRadio1" checked>
                                <label class="check-label" for="docRadio1">Radio 1</label>
                            </div>
                            <div class="check-option">
                                <input class="check-input" type="radio" name="docRadio" id="docRadio2">
                                <label class="check-label" for="docRadio2">Radio 2</label>
                            </div>
                        </div>
                    </div>

                    <h5 class="mt-4">Validation States</h5>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="field-group">
                                <label class="field-label">Valid</label>
                                <input type="text" class="input-control state-valid" value="Valid input">
                                <div class="field-text state-valid">Looks good!</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="field-group">
                                <label class="field-label">Invalid</label>
                                <input type="text" class="input-control state-invalid" value="Bad input">
                                <div class="field-text state-invalid">Please fix this field</div>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-muted">JS validation: <code>OS.validateForm('formId')</code> checks <code>required</code>, <code>email</code>, pattern, <code>minlength</code>, <code>maxlength</code>.</p>
                </div>
            </div>
        </section>

        <!-- ========== TABLES ========== -->
        <section id="tables">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Tables</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Use <code>.data-table</code> for base styling. Wrap in <code>.responsive-table</code> for horizontal scroll on small screens.</p>
                    <div class="responsive-table mb-4">
                        <table class="data-table data-table-striped data-table-hover">
                            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Role</th></tr></thead>
                            <tbody>
                                <tr><td>1</td><td>Alice</td><td>alice@example.com</td><td>Admin</td></tr>
                                <tr><td>2</td><td>Bob</td><td>bob@example.com</td><td>Editor</td></tr>
                                <tr><td>3</td><td>Carol</td><td>carol@example.com</td><td>Viewer</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <h5>Table Variants</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-light border">.data-table</span>
                        <span class="badge bg-light border">.data-table-striped</span>
                        <span class="badge bg-light border">.data-table-bordered</span>
                        <span class="badge bg-light border">.data-table-hover</span>
                        <span class="badge bg-light border">.data-table-success</span>
                        <span class="badge bg-light border">.data-table-warning</span>
                        <span class="badge bg-light border">.data-table-danger</span>
                        <span class="badge bg-light border">.data-table-info</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== UTILITIES ========== -->
        <section id="utilities">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Utilities</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h5>Display</h5>
                            <p><code>.d-none</code>, <code>.d-block</code>, <code>.d-inline</code>, <code>.d-inline-block</code>, <code>.d-flex</code>, <code>.d-grid</code></p>
                            <p class="mt-2">Responsive: <code>.d-sm-*</code>, <code>.d-md-*</code>, <code>.d-lg-*</code>, <code>.d-xl-*</code></p>
                            <p class="mt-2">Print: <code>.d-print-none</code>, <code>.d-print-block</code>, <code>.d-print-flex</code>, <code>.d-print-inline</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Spacing</h5>
                            <p><code>.m-0</code> through <code>.m-5</code> | <code>.mt-*</code>, <code>.mb-*</code>, <code>.ms-*</code>, <code>.me-*</code></p>
                            <p><code>.p-0</code> through <code>.p-4</code> | <code>.pt-*</code>, <code>.pb-*</code>, <code>.px-*</code>, <code>.py-*</code></p>
                            <p><code>.mx-auto</code>, <code>.ml-auto</code>, <code>.mr-auto</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Sizing</h5>
                            <p><code>.w-25/50/75/100/auto</code>, <code>.mw-100</code>, <code>.vw-100</code>, <code>.min-vw-100</code></p>
                            <p><code>.h-25/50/75/100/auto</code>, <code>.mh-100</code>, <code>.vh-100</code>, <code>.min-vh-100</code></p>
                            <p><code>.min-h-screen</code> (100vh)</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Borders</h5>
                            <p><code>.border</code>, <code>.border-0/2/4</code>, <code>.border-start</code>, <code>.border-end</code>, <code>.border-top</code>, <code>.border-bottom</code></p>
                            <p><code>.border-success/warning/danger/info/purple/dark/light</code></p>
                            <p><code>.border-none</code>, <code>.rounded</code>, <code>.rounded-circle</code>, <code>.rounded-pill</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Position</h5>
                            <p><code>.position-relative/absolute/fixed/sticky</code></p>
                            <p><code>.fixed-top</code>, <code>.fixed-bottom</code>, <code>.sticky-top</code></p>
                            <p><code>.top-0/50</code>, <code>.bottom-0/50</code>, <code>.start-0/50</code>, <code>.end-0/50</code></p>
                            <p><code>.translate-middle</code>, <code>.translate-middle-x/y</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Backgrounds</h5>
                            <p><code>.bg-primary/secondary/success/warning/danger/info</code></p>
                            <p><code>.bg-purple/pink/cyan/indigo/teal</code></p>
                            <p><code>.bg-light</code>, <code>.bg-dark</code>, <code>.bg-transparent</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Overflow & Opacity</h5>
                            <p><code>.overflow-auto/hidden/scroll/visible</code></p>
                            <p><code>.opacity-0/25/50/75/100</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Visibility & Interaction</h5>
                            <p><code>.visible</code>, <code>.invisible</code></p>
                            <p><code>.user-select-all/auto/none</code></p>
                            <p><code>.pe-none</code>, <code>.pe-auto</code></p>
                            <p><code>.visually-hidden</code>, <code>.visually-hidden-focusable</code></p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Other</h5>
                            <p><code>.float-start/end/none</code>, <code>.clearfix</code></p>
                            <p><code>.object-fit-contain/cover/fill</code></p>
                            <p><code>.z-0/1/2/3</code></p>
                            <p><code>.stretched-link</code></p>
                            <p><code>.shadow-sm</code>, <code>.shadow</code>, <code>.shadow-lg</code>, <code>.shadow-none</code></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== JAVASCRIPT ========== -->
        <section id="js">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">JavaScript API</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>All OwnStrap JS is available through the global <code class="fw-bold">OS</code> object.</p>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h5>Modals</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.openModal(id)</code> — open modal</li>
                                <li class="list-group-item"><code>OS.closeModal(id)</code> — close modal</li>
                                <li class="list-group-item"><code>OS.toggleModal(id)</code> — toggle modal</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Toasts</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.success(msg)</code> — green toast</li>
                                <li class="list-group-item"><code>OS.warning(msg)</code> — orange toast</li>
                                <li class="list-group-item"><code>OS.error(msg)</code> — red toast</li>
                                <li class="list-group-item"><code>OS.info(msg)</code> — blue toast</li>
                                <li class="list-group-item"><code>OS.purple/pink/cyan/indigo/teal(msg)</code></li>
                                <li class="list-group-item"><code>OS.showToast(msg, type, duration)</code></li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Tabs</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.switchTab(tabId, containerId)</code></li>
                            </ul>
                            <p class="text-sm text-muted">Arrow keys navigate between tabs. Use <code>data-tab</code> and <code>data-tabs</code> attributes.</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Accordion</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.toggleAccordionItem(itemId)</code></li>
                            </ul>
                            <p class="text-sm text-muted">Use <code>data-single</code> to allow only one open item. Enter/Space keys toggle.</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Dropdown</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.toggleDropdown(id)</code></li>
                                <li class="list-group-item"><code>OS.closeAllDropdowns()</code></li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Collapse</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.toggleCollapse(id)</code></li>
                                <li class="list-group-item"><code>OS.showCollapse(id)</code></li>
                                <li class="list-group-item"><code>OS.hideCollapse(id)</code></li>
                            </ul>
                            <p class="text-sm text-muted">Use <code>data-toggle="collapse" data-target="id"</code> on trigger buttons.</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Carousel</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.nextCarousel(id)</code></li>
                                <li class="list-group-item"><code>OS.prevCarousel(id)</code></li>
                                <li class="list-group-item"><code>OS.showCarouselItem(id, index)</code></li>
                                <li class="list-group-item"><code>OS.startCarousel(id)</code></li>
                                <li class="list-group-item"><code>OS.pauseCarousel(id)</code></li>
                            </ul>
                            <p class="text-sm text-muted">Use <code>data-carousel="id" data-interval="5000" data-pause="true"</code> attributes.</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Progress & Spinners</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.setProgress(id, percentage)</code></li>
                                <li class="list-group-item"><code>OS.animateProgress(id, from, to, duration)</code></li>
                                <li class="list-group-item"><code>OS.showSpinner(containerId)</code></li>
                                <li class="list-group-item"><code>OS.hideSpinner(containerId)</code></li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Form Validation</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.validateForm(formId)</code> — validates required, email, pattern, minlength, maxlength</li>
                                <li class="list-group-item"><code>OS.clearForm(formId)</code> — reset form and validation states</li>
                                <li class="list-group-item"><code>OS.getFormData(formId)</code> — returns form data as object</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>HTTP Utilities</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>await OS.get(url)</code> — GET request</li>
                                <li class="list-group-item"><code>await OS.post(url, data)</code> — POST request</li>
                                <li class="list-group-item"><code>await OS.put(url, data)</code> — PUT request</li>
                                <li class="list-group-item"><code>await OS.del(url)</code> — DELETE request</li>
                            </ul>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h5>Storage & Utilities</h5>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item"><code>OS.storage.get/set/remove/clear(key, value)</code> — localStorage wrapper</li>
                                <li class="list-group-item"><code>OS.cookie.get/set/remove(name, value, days)</code> — cookie wrapper</li>
                                <li class="list-group-item"><code>OS.copyToClipboard(text)</code></li>
                                <li class="list-group-item"><code>OS.smoothScroll(elementId)</code></li>
                                <li class="list-group-item"><code>OS.debounce(fn, delay)</code> / <code>OS.throttle(fn, limit)</code></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== ACCESSIBILITY ========== -->
        <section id="accessibility">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Accessibility</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Built-in Features</h5>
                            <ul>
                                <li><code>:focus-visible</code> outline (2px solid <code>#00A8E8</code>, 2px offset)</li>
                                <li>Focus trap in modals (Tab/Shift+Tab cycle)</li>
                                <li>Keyboard nav for tabs (Arrow Left/Right)</li>
                                <li>Keyboard nav for accordion (Enter/Space)</li>
                                <li><code>aria-expanded</code> dynamically set on dropdowns, collapse, accordion</li>
                                <li><code>aria-haspopup</code> on dropdown toggles</li>
                                <li><code>role="button"</code> on accordion headers</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Utilities</h5>
                            <ul>
                                <li><code>.visually-hidden</code> — screen-reader only content</li>
                                <li><code>.visually-hidden-focusable</code> — visible on focus</li>
                                <li><code>prefers-reduced-motion</code> — disables animations</li>
                                <li>Print styles — hides navbar, adds link URLs, removes shadows</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<!-- Doc Modal -->
<div id="docModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5>Documentation Modal</h5>
            <button class="modal-close" onclick="OS.closeModal('docModal')">&times;</button>
        </div>
        <div>
            <p>This modal demonstrates focus trapping. Tab through the buttons below:</p>
            <button class="btn btn-primary" onclick="OS.closeModal('docModal')">Close</button>
            <button class="btn btn-info ms-2" onclick="OS.info('Button clicked')">Info</button>
        </div>
    </div>
</div>

<script>
(function(){for(var e=document.querySelector("aside"),t=e.querySelectorAll('a[href^="#"]'),n=[],o=0;o<t.length;o++){var r=t[o].getAttribute("href").slice(1),l=document.getElementById(r);l&&n.push({id:r,el:l})}function i(){var e=window.scrollY+250,o="";n.forEach(function(t){t.el.offsetTop<=e&&(o=t.id)}),t.forEach(function(e){e.classList.toggle("active",e.getAttribute("href")==="#"+o)})}window.addEventListener("scroll",i,{passive:!0}),i()})();
</script>
<?php ownstrap_js(); ?>
</body>
</html>