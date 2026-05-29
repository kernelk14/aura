<?php include 'template.php'; ?>

<div class="d-flex">
    <?php template('sidebar-framework'); ?>

    <main class="flex-grow-1 ps-4 pb-4 ms-2" style="min-width:0;max-width:1400px;">
        <h1 class="fw-bolder text-4xl mt-3 mb-1">AuraPHP Framework</h1>
        <p class="lead mb-4">Complete guide to building applications with AuraPHP — a lightweight PHP MVC framework with OwnStrap CSS.</p>

        <!-- ============ INSTALLATION ============ -->
        <section id="installation">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Installation</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Via Composer (recommended)</h5>
                    <pre>composer create-project auraphp/auraphp my-app
cd my-app
php aura serve</pre>

                    <h5>Manual Installation</h5>
                    <p>Clone the repository and point your web server to the project root:</p>
                    <pre>git clone https://github.com/auraphp/auraphp.git
cd auraphp
php aura serve</pre>

                    <h5>Requirements</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-dark">PHP >= 7.4</span>
                        <span class="badge bg-dark">Web Server (Apache/Nginx/PHP built-in)</span>
                        <span class="badge bg-dark">Database (MySQL/PostgreSQL/SQLite) — optional</span>
                    </div>

                    <h5 class="mt-3">Start the development server</h5>
                    <pre>php aura serve
# Opens at http://127.0.0.1:8080</pre>
                    <p class="text-muted text-sm mt-1">Custom port: <code>php aura serve --port=3000 --host=0.0.0.0</code></p>
                </div>
            </div>
        </section>

        <!-- ============ STRUCTURE ============ -->
        <section id="structure">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Project Structure</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <pre>my-app/
├── aura                    # CLI tool
├── composer.json
├── index.php               # Entry point
├── .htaccess               # Apache rewrite rules
├── public/
│   ├── css/                # OwnStrap stylesheets
│   ├── js/                 # OwnStrap JavaScript
│   └── fonts/              # Inter & Fira Sans fonts
├── site/
│   ├── controllers/        # Your controllers
│   ├── models/             # Your models
│   └── views/              # Your views
│       └── template.php    # Base HTML template
└── system/
    ├── core/               # Framework core (Router, Base, Database)
    ├── config/             # Configuration (app, database, routes)
    └── helpers/            # Global helper functions</pre>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h5><code>site/</code> — Your Application</h5>
                            <p>All your application code goes here: controllers that handle logic, models that interact with the database, and views that render HTML.</p>
                        </div>
                        <div class="col-md-6">
                            <h5><code>system/</code> — Framework Core</h5>
                            <p>The framework files you generally don't modify. Contains the router, base classes, configuration, and helpers.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ ROUTING ============ -->
        <section id="routing">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Routing</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Define routes in <code>system/config/routes.php</code>. The router supports GET, POST, PUT, DELETE, and wildcard methods.</p>

                    <h5>Basic routes</h5>
                    <pre>$router->get('/', 'welcome@index');
$router->get('about', 'page@about');
$router->post('contact', 'contact@store');
$router->put('users/:id', 'user@update');
$router->delete('users/:id', 'user@destroy');
$router->any('webhook', 'webhook@handle');</pre>

                    <h5>Route parameters</h5>
                    <p>Use <code>:param</code> syntax. Parameters are passed as positional arguments to the controller method:</p>
                    <pre>// Route
$router->get('blog/:id/:slug', 'blog@show');

// Controller receives $id and $slug
class Blog extends Controller {
    public function show($id, $slug) {
        echo "Post #{$id}: {$slug}";
    }
}</pre>

                    <h5>Closure/callback routes</h5>
                    <pre>$router->get('api/time', function() {
    echo json_encode(['time' => date('Y-m-d H:i:s')]);
});</pre>

                    <h5>Available methods</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-success">GET</span>
                        <span class="badge bg-warning text-dark">POST</span>
                        <span class="badge bg-info">PUT</span>
                        <span class="badge bg-danger">DELETE</span>
                        <span class="badge bg-dark">ANY</span>
                    </div>

                    <h5 class="mt-3">List routes</h5>
                    <pre>php aura route:list</pre>
                </div>
            </div>
        </section>

        <!-- ============ CONTROLLERS ============ -->
        <section id="controllers">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Controllers</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Controllers live in <code>site/controllers/</code> and extend <code>AuraCore\Controller</code>.</p>

                    <h5>Naming conventions</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered">
                            <thead><tr><th>Item</th><th>Convention</th><th>Example</th></tr></thead>
                            <tbody>
                                <tr><td>File name</td><td>Lowercase kebab</td><td><code>user-profile.php</code></td></tr>
                                <tr><td>Class name</td><td>PascalCase</td><td><code>UserProfile</code></td></tr>
                                <tr><td>Namespace</td><td><code>SiteControllers</code></td><td><code>SiteControllers\UserProfile</code></td></tr>
                                <tr><td>Route handler</td><td><code>file@method</code></td><td><code>user-profile@index</code></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3">Example controller</h5>
                    <pre>&lt;?php
// site/controllers/user-profile.php

namespace SiteControllers;

use AuraCore\Controller;

class UserProfile extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'User Profiles',
            'users' => ['Alice', 'Bob'],
        ];
        $this-&gt;loadView('user-profile', $data);
    }

    public function show($id)
    {
        $db = $this-&gt;loadDatabase();
        $user = $db-&gt;getWhere('users', ['id' =&gt; $id]);

        $this-&gt;loadView('user-profile/show', [
            'title' =&gt; 'User Details',
            'user' =&gt; $user,
        ]);
    }
}</pre>

                    <h5 class="mt-3">Available methods in controllers</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered">
                            <thead><tr><th>Method</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>$this-&gt;loadView('name', $data)</code></td><td>Load a view with extracted data</td></tr>
                                <tr><td><code>$this-&gt;loadModel('user_model')</code></td><td>Load a model instance</td></tr>
                                <tr><td><code>$this-&gt;loadDatabase($group)</code></td><td>Load a database connection</td></tr>
                                <tr><td><code>$this-&gt;redirect('url')</code></td><td>Redirect to a URL</td></tr>
                                <tr><td><code>$this-&gt;config('item')</code></td><td>Get a configuration value</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3">Generate controllers with the CLI</h5>
                    <pre>php aura make:controller UserProfile</pre>
                    <p class="text-sm text-muted">Use <code>--resource</code> to generate all 7 REST methods (index, create, store, show, edit, update, destroy).</p>
                </div>
            </div>
        </section>

        <!-- ============ VIEWS ============ -->
        <section id="views">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Views</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Views are plain PHP files in <code>site/views/</code>. Data passed from controllers is available as extracted variables.</p>

                    <h5>Template pattern</h5>
                    <p>Every view should follow this structure:</p>
                    <pre>&lt;?php include 'template.php'; ?&gt;   &lt;!-- HTML shell (head, body open) --&gt;

&lt;div class="container mt-5"&gt;
    &lt;h1&gt;&lt;?= htmlspecialchars($title) ?&gt;&lt;/h1&gt;
    &lt;p&gt;&lt;?= htmlspecialchars($message) ?&gt;&lt;/p&gt;
&lt;/div&gt;

&lt;?php ownstrap_js(); ?&gt;            &lt;!-- JavaScript --&gt;
&lt;/body&gt;
&lt;/html&gt;</pre>

                    <h5>Passing data from controllers</h5>
                    <pre>// Controller
$this-&gt;loadView('user-profile', [
    'title' =&gt; 'User Profile',
    'user' =&gt; ['name' =&gt; 'Alice', 'email' =&gt; 'alice@example.com'],
]);

// View receives $title and $user
&lt;h1&gt;&lt;?= htmlspecialchars($title) ?&gt;&lt;/h1&gt;
&lt;p&gt;&lt;?= htmlspecialchars($user['name']) ?&gt;&lt;/p&gt;</pre>

                    <h5>Helper functions available in views</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered">
                            <thead><tr><th>Function</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>&lt;?php ownstrap_css() ?&gt;</code></td><td>Outputs CSS &lt;link&gt; tags</td></tr>
                                <tr><td><code>&lt;?php ownstrap_js() ?&gt;</code></td><td>Outputs JS &lt;script&gt; tag</td></tr>
                                <tr><td><code>&lt;?= site_url('path') ?&gt;</code></td><td>Full URL to a path</td></tr>
                                <tr><td><code>&lt;?= base_url('path') ?&gt;</code></td><td>Base URL with path</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3">Generate views with the CLI</h5>
                    <pre>php aura make:view about-us</pre>
                </div>
            </div>
        </section>

        <!-- ============ MODELS ============ -->
        <section id="models">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Models</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Models live in <code>site/models/</code> and extend <code>AuraCore\Model</code>.</p>

                    <pre>&lt;?php
// site/models/user_model.php

use AuraCore\Model;

class User_Model extends Model
{
    private $table = 'users';

    public function getAll()
    {
        $db = $this-&gt;loadDatabase();
        return $db-&gt;get($this-&gt;table);
    }

    public function getById($id)
    {
        $db = $this-&gt;loadDatabase();
        return $db-&gt;getWhere($this-&gt;table, ['id' =&gt; $id]);
    }

    public function create($data)
    {
        $db = $this-&gt;loadDatabase();
        return $db-&gt;insert($this-&gt;table, $data);
    }

    public function update($id, $data)
    {
        $db = $this-&gt;loadDatabase();
        return $db-&gt;update($this-&gt;table, $data, ['id' =&gt; $id]);
    }

    public function delete($id)
    {
        $db = $this-&gt;loadDatabase();
        return $db-&gt;delete($this-&gt;table, ['id' =&gt; $id]);
    }
}</pre>

                    <h5>Usage in a controller</h5>
                    <pre>$userModel = $this-&gt;loadModel('user_model');
$users = $userModel-&gt;getAll();</pre>

                    <h5 class="mt-3">Generate models with the CLI</h5>
                    <pre>php aura make:model User</pre>
                </div>
            </div>
        </section>

        <!-- ============ DATABASE ============ -->
        <section id="database">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Database</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5>Configuration</h5>
                    <p>Edit <code>system/config/database.php</code>:</p>
                    <pre>return [
    'default' => [
        'driver'   => 'pdo_mysql',   // mysqli, pdo_mysql, pdo_pgsql, pdo_sqlite
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'secret',
        'database' => 'my_app',
        'charset'  => 'utf8',
    ],
];</pre>

                    <h5>Supported drivers</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-success">mysqli</span> MySQL via mysqli
                        <span class="badge bg-warning text-dark ms-1">pdo_mysql</span> MySQL via PDO
                        <span class="badge bg-info ms-1">pdo_pgsql</span> PostgreSQL via PDO
                        <span class="badge bg-secondary ms-1">pdo_sqlite</span> SQLite via PDO
                    </div>

                    <h5 class="mt-3">Usage</h5>
                    <pre>$db = $this-&gt;loadDatabase();

// Get all rows
$users = $db-&gt;get('users');

// Get with WHERE
$user = $db-&gt;getWhere('users', ['id' => 1]);

// Insert (returns insert ID)
$id = $db-&gt;insert('users', [
    'name' => 'Alice',
    'email' => 'alice@example.com',
]);

// Update
$db-&gt;update('users', ['name' => 'Bob'], ['id' => 1]);

// Delete
$db-&gt;delete('users', ['id' => 1]);

// Raw SQL query
$results = $db-&gt;query('SELECT * FROM users WHERE active = 1');

// Last insert ID / affected rows
$id = $db-&gt;lastInsertId();
$count = $db-&gt;affectedRows();</pre>

                    <h5 class="mt-3">Multiple database connections</h5>
                    <p>Define multiple groups in <code>database.php</code> and reference them by name:</p>
                    <pre>$analytics = $this-&gt;loadDatabase('analytics');
$logs = $this-&gt;loadDatabase('logs');</pre>
                </div>
            </div>
        </section>

        <!-- ============ CLI TOOL ============ -->
        <section id="cli">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">The aura CLI Tool</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>AuraPHP includes a command-line tool at the project root. Run it with <code>php aura</code>.</p>
                    <pre>php aura list</pre>

                    <h5>Available commands</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered data-table-hover">
                            <thead><tr><th>Command</th><th>Description</th><th>Example</th></tr></thead>
                            <tbody>
                                <tr><td><code>php aura serve</code></td><td>Start PHP dev server</td><td><code>php aura serve --port=3000</code></td></tr>
                                <tr><td><code>php aura route:list</code></td><td>Display all routes</td><td><code>php aura route:list</code></td></tr>
                                <tr><td><code>php aura make:controller</code></td><td>Generate a controller</td><td><code>php aura make:controller Product --resource</code></td></tr>
                                <tr><td><code>php aura make:model</code></td><td>Generate a model</td><td><code>php aura make:model User</code></td></tr>
                                <tr><td><code>php aura make:view</code></td><td>Generate a view</td><td><code>php aura make:view about</code></td></tr>
                                <tr><td><code>php aura key:generate</code></td><td>Generate APP_KEY in .env</td><td><code>php aura key:generate</code></td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3"><code>make:controller --resource</code></h5>
                    <p>The <code>--resource</code> flag generates all 7 RESTful methods in one command:</p>
                    <pre>php aura make:controller Product --resource
# Generates: index(), create(), store(), show($id),
#            edit($id), update($id), destroy($id)</pre>

                    <h5 class="mt-3"><code>make:model</code></h5>
                    <p>Generates a model with built-in CRUD methods for a database table:</p>
                    <pre>php aura make:model User
# Creates site/models/user_model.php with:
# getAll(), getById($id), create($data),
# update($id, $data), delete($id)</pre>

                    <h5 class="mt-3"><code>route:list</code> output</h5>
                    <pre>Method  Path                Handler
────────────────────────────────────────
GET     /                   welcome@index
GET     /components         components@index
GET     /documentation      documentation@index
GET     /docs               docs@index
GET     /demo               demo@index
GET     /demo/user/:id      demo@user</pre>
                </div>
            </div>
        </section>

        <!-- ============ CONFIGURATION ============ -->
        <section id="configuration">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Configuration</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Application config</h5>
                            <p><code>system/config/config.php</code></p>
                            <pre>&lt;?php
return [
    'base_url' => 'http://localhost:8080',
];</pre>
                            <p class="text-sm text-muted">Access: <code>$this->config('base_url')</code></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Database config</h5>
                            <p><code>system/config/database.php</code></p>
                            <pre>&lt;?php
return [
    'default' => [
        'driver'   => 'pdo_mysql',
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'my_app',
    ],
];</pre>
                            <p class="text-sm text-muted">Access: <code>$this->config('database.default')</code></p>
                        </div>
                    </div>

                    <h5 class="mt-3">Routes configuration</h5>
                    <p><code>system/config/routes.php</code> — all route definitions go here:</p>
                    <pre>&lt;?php

use AuraCore\Router;

$router = new Router();

$router-&gt;get('/', 'welcome@index');
$router-&gt;get('about', 'about@index');
$router-&gt;post('contact', 'contact@store');

// Add your routes below

return $router;</pre>
                </div>
            </div>
        </section>

        <!-- ============ OWNSTRAP ============ -->
        <section id="ownstrap">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">OwnStrap CSS/JS Framework</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>AuraPHP ships with <strong>OwnStrap</strong>, a lightweight CSS/JS framework providing 300+ utility classes and 10+ interactive components — no external dependencies.</p>

                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="badge bg-info p-2">300+ Utilities</span>
                        <span class="badge bg-success p-2">10+ Components</span>
                        <span class="badge bg-warning text-dark p-2">12-Column Grid</span>
                        <span class="badge bg-purple p-2">Zero Dependencies</span>
                    </div>

                    <h5>Quick include</h5>
                    <pre>&lt;?php ownstrap_css(); ?&gt;   &lt;!-- In &lt;head&gt; --&gt;
&lt;?php ownstrap_js(); ?&gt;    &lt;!-- Before &lt;/body&gt; --&gt;</pre>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>CSS features</h5>
                            <ul>
                                <li>Themes: <code>theme-dark</code>, <code>theme-light</code></li>
                                <li>Grid: responsive 12-column with 4 breakpoints</li>
                                <li>Typography: 8 sizes, 5 weights, gradients</li>
                                <li>Colors: 9 semantic colors + shades</li>
                                <li>Components: cards, buttons, badges, alerts, modals, tabs, accordions, dropdowns, toasts, navbar, pagination, breadcrumbs, progress, spinners, carousels, tooltips, collapse</li>
                                <li>Utilities: spacing, sizing, borders, shadows, display, flex, position, opacity, z-index, overflow, visibility</li>
                                <li>Accessibility: focus-visible, reduced-motion, print styles, screen-reader utilities</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>JavaScript features</h5>
                            <ul>
                                <li>Modals with focus trap and Escape key</li>
                                <li>Tabs with arrow key navigation</li>
                                <li>Accordions with keyboard support</li>
                                <li>Dropdowns with click-outside close</li>
                                <li>Toasts (success, warning, error, info + 5 extra colors)</li>
                                <li>Collapse with data-attribute triggers</li>
                                <li>Carousel with auto-play and pause-on-hover</li>
                                <li>Tooltips on hover/focus</li>
                                <li>Form validation (required, email, pattern, min/max length)</li>
                                <li>HTTP wrapper (GET, POST, PUT, DELETE)</li>
                                <li>Storage utilities (localStorage + cookies)</li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <a href="<?= site_url('docs/ownstrap') ?>" class="btn btn-info">Full OwnStrap Documentation</a>
                        <a href="<?= site_url('components') ?>" class="btn btn-success">Component Demos</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============ SERVER CONFIG ============ -->
        <section id="server">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Server Configuration</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Apache (.htaccess)</h5>
                            <p>The included <code>.htaccess</code> handles URL rewriting:</p>
                            <pre>RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]</pre>
                        </div>
                        <div class="col-md-6">
                            <h5>Nginx</h5>
                            <pre>server {
    listen 80;
    root /var/www/my-app;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}</pre>
                        </div>
                    </div>

                    <h5 class="mt-3">PHP Built-in Server</h5>
                    <pre>php aura serve</pre>
                    <p class="text-sm text-muted">Runs at <code>http://127.0.0.1:8080</code> by default.</p>
                </div>
            </div>
        </section>

        <!-- ============ QUICK REFERENCE ============ -->
        <section id="quickref">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Quick Reference</h2>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Create a route</h5>
                            <div class="text-sm"><code>$router->get('path', 'ctrl@method');</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Create a controller</h5>
                            <div class="text-sm"><code>php aura make:controller Name</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Create a model</h5>
                            <div class="text-sm"><code>php aura make:model Name</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Load a view</h5>
                            <div class="text-sm"><code>$this->loadView('name', $data);</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Query database</h5>
                            <div class="text-sm"><code>$db = $this->loadDatabase();<br>$db->get('table');</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Start dev server</h5>
                            <div class="text-sm"><code>php aura serve</code></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
(function(){for(var e=document.querySelector("aside"),t=e.querySelectorAll('a[href^="#"]'),n=[],o=0;o<t.length;o++){var r=t[o].getAttribute("href").slice(1),l=document.getElementById(r);l&&n.push({id:r,el:l})}function i(){var e=window.scrollY+250,o="";n.forEach(function(t){t.el.offsetTop<=e&&(o=t.id)}),t.forEach(function(e){e.classList.toggle("active",e.getAttribute("href")==="#"+o)})}window.addEventListener("scroll",i,{passive:!0}),i()})();
</script>
<?php ownstrap_js(); ?>
</body>
</html>