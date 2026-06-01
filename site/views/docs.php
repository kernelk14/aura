<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'AuraPHP Framework') ?></title>
    <?php ownstrap_css(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css" id="hljs-theme">
    <style>
        pre { position: relative; padding: 1.25rem 1rem 0.75rem; background: #f6f8fa; border-color: #d0d7de; }
        pre code { background: none; padding: 0; font-size: 0.8125rem; }
        pre::before {
            content: attr(data-lang);
            position: absolute; top: 0.25rem; right: 0.75rem;
            font-size: 0.6875rem; font-weight: 600; text-transform: uppercase;
            color: #8b949e; letter-spacing: 0.5px;
        }
        .theme-dark pre { background: #161b22; border-color: #30363d; }
        .theme-dark pre code.hljs { background: none; }
        .hljs { background: none; padding: 0; }
    </style>
</head>
<body class="theme-light">

<?php template('navbar');?>
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
│   ├── templates/          # Template partials
│   │   ├── sidebar-framework.php
│   │   └── sidebar-ownstrap.php
│   └── views/              # Your views
└── system/
    ├── core/               # Framework core (Router, Base, Database)
    │   └── content-wrapper.php  # Base HTML layout
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
                                <tr><td><code>$this-&gt;request()</code></td><td>Get the current Request object</td></tr>
                                <tr><td><code>$this-&gt;response()</code></td><td>Get a Response builder instance</td></tr>
                                <tr><td><code>$this-&gt;session()</code></td><td>Get a Session instance with flash messages</td></tr>
                                <tr><td><code>$this-&gt;validate($data, $rules)</code></td><td>Validate data with the Validator</td></tr>
                                <tr><td><code>$this-&gt;auth($modelClass)</code></td><td>Get the Auth instance</td></tr>
                                <tr><td><code>$this-&gt;json($data, $status)</code></td><td>Return a JSON response</td></tr>
                                <tr><td><code>$this-&gt;back()</code></td><td>Redirect back to the previous page</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3">Request object</h5>
                    <pre>public function store()
{
    $request = $this-&gt;request();

    // Get input values
    $name  = $request-&gt;input('name');
    $email = $request-&gt;input('email', 'default@example.com');

    // Get all input as array
    $all = $request-&gt;all();

    // Get only specific fields
    $data = $request-&gt;only(['name', 'email']);

    // Check if field exists
    if ($request-&gt;has('email')) { ... }

    // Get HTTP method
    $method = $request-&gt;method();  // 'GET', 'POST', etc.

    // Check if AJAX request
    if ($request-&gt;ajax()) { ... }

    // Get uploaded file
    $file = $request-&gt;file('avatar');

    // Magic property access
    $name = $request-&gt;name;
}</pre>

                    <h5 class="mt-3">Response object</h5>
                    <pre>public function api()
{
    // JSON response
    return $this-&gt;json(['status' => 'ok'], 200);

    // Custom status code
    return $this-&gt;response()
        -&gt;status(201)
        -&gt;json(['id' => 123]);

    // Redirect with flash message
    return $this-&gt;response()
        -&gt;redirect('/dashboard')
        -&gt;with('success', 'Welcome back!');

    // Redirect back with input
    return $this-&gt;response()
        -&gt;back()
        -&gt;withInput();
}

// In your view, display flash messages:
// &lt;?php $session = new AuraCore\Session(); ?&gt;
// &lt;?php if ($flash = $session-&gt;flash('success')): ?&gt;
//     &lt;div class="alert alert-success"&gt;&lt;?= $flash ?&gt;&lt;/div&gt;
// &lt;?php endif; ?&gt;</pre>

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
                    <pre>&lt;div class="container mt-5"&gt;
    &lt;h1&gt;&lt;?= htmlspecialchars($title) ?&gt;&lt;/h1&gt;
    &lt;p&gt;&lt;?= htmlspecialchars($message) ?&gt;&lt;/p&gt;
&lt;/div&gt;</pre>
                    <p>Views are pure content — the layout (<code>site/templates/template.php</code>) wraps them automatically. To skip the layout, pass <code>false</code> as the third argument: <code>$this-&gt;loadView('name', $data, false)</code>.</p>

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
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Models (ActiveRecord)</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Models live in <code>site/models/</code> and extend <code>AuraCore\Model</code>. Each model maps to a database table — the table name is automatically derived from the class name (e.g. <code>User</code> &rarr; <code>users</code>, <code>BlogPost</code> &rarr; <code>blog_posts</code>). You can override it with the <code>$table</code> property.</p>

                    <h5>Basic model</h5>
                    <pre>&lt;?php
// site/models/user.php

use AuraCore\Model;

class User extends Model
{
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password'];
    protected $casts = [
        'is_active' =&gt; 'bool',
    ];
}</pre>

                    <h5>Querying</h5>
                    <pre>// Get all records
$users = User::all();

// Find by primary key
$user = User::find(1);

// Find multiple
$users = User::findMany([1, 2, 3]);

// Fluent where clauses
$activeUsers = User::where('is_active', 1)-&gt;get();
$adults = User::where('age', '&gt;=', 18)-&gt;orderBy('name')-&gt;get();
$user = User::where('email', 'alice@example.com')-&gt;first();

// Count, exists, aggregates
$count = User::where('is_active', 1)-&gt;count();
$exists = User::where('email', $email)-&gt;exists();
$avg = User::avg('age');</pre>

                    <h5>Creating &amp; updating</h5>
                    <pre>// Create and save
$user = new User();
$user-&gt;name = 'Alice';
$user-&gt;email = 'alice@example.com';
$user-&gt;save();

// Create with attributes
$user = User::create([
    'name' =&gt; 'Bob',
    'email' =&gt; 'bob@example.com',
]);

// Find and update
$user = User::find(1);
$user-&gt;name = 'Alice Smith';
$user-&gt;save();

// Update or create
$user = User::updateOrCreate(
    ['email' =&gt; 'alice@example.com'],
    ['name' =&gt; 'Alice']
);

// First or create
$user = User::firstOrCreate(
    ['email' =&gt; 'alice@example.com']
);</pre>

                    <h5>Deleting</h5>
                    <pre>$user = User::find(1);
$user-&gt;delete();

// Delete by ID
$user = new User();
$user-&gt;destroy(1);
$user-&gt;destroy([1, 2, 3]);</pre>

                    <h5>Timestamps</h5>
                    <p>By default, <code>created_at</code> and <code>updated_at</code> are managed automatically. Disable with <code>public $timestamps = false;</code>.</p>

                    <h5>Attribute casting</h5>
                    <pre>protected $casts = [
    'id'        =&gt; 'int',
    'is_active' =&gt; 'bool',
    'score'     =&gt; 'float',
    'meta'      =&gt; 'json',   // auto-encodes/decodes
    'birthday'  =&gt; 'date',   // returns DateTime object
];</pre>

                    <h5>Relationships</h5>
                    <pre>class User extends Model
{
    public function posts()
    {
        return $this-&gt;hasMany(Post::class, 'user_id');
    }

    public function profile()
    {
        return $this-&gt;hasOne(Profile::class, 'user_id');
    }
}

class Post extends Model
{
    public function author()
    {
        return $this-&gt;belongsTo(User::class, 'user_id');
    }

    public function tags()
    {
        return $this-&gt;belongsToMany(Tag::class, 'post_tags');
    }
}

// Usage
$posts = User::find(1)-&gt;posts()-&gt;get();
$author = Post::find(1)-&gt;author()-&gt;first();</pre>

                    <h5>Eager loading</h5>
                    <pre>// Lazy loading (N+1 query)
$users = User::all();
foreach ($users as $user) {
    echo $user->load('posts');  // One query per user
}

// Eager loading (2 queries total)
$users = User::with('posts')->get();
foreach ($users as $user) {
    echo $user->getRelation('posts');  // No additional queries
}

// Eager load specific relations
$users = User::with('posts', 'profile')->get();
$user = User::with('posts')->find(1);

// Lazy eager load on existing models
$user = User::find(1);
$user->load('posts', 'profile');</pre>

                    <h5>Soft deletes</h5>
                    <pre>use AuraCore\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}

// Soft delete (sets deleted_at, does not remove row)
$post = Post::find(1);
$post->delete();  // Sets deleted_at timestamp

// Force delete (removes from database)
$post->forceDelete();

// Include soft-deleted records
$posts = Post::withTrashed()->get();

// Only soft-deleted records
$trashed = Post::onlyTrashed()->get();

// Restore a soft-deleted record
$post->restore();

// Check if soft-deleted
if ($post->trashed()) { ... }</pre>

                    <h5>Global scopes</h5>
                    <pre class="language-php">// Add a global scope (e.g., in a model's boot method)
User::addGlobalScope(function ($query) {
    $query->where('is_active', 1);
});

// Query without global scopes
$allUsers = User::withoutGlobalScopes()->get();</pre>

                    <h5>Serialization</h5>
                    <pre>$user = User::find(1);

// To array
$data = $user->toArray();

// To JSON
$json = $user->toJson();

// Hidden attributes are excluded automatically
protected $hidden = ['password'];</pre>

                    <h5 class="mt-3">Generate models with the CLI</h5>
                    <pre>php aura make:model User</pre>
                    <p class="text-sm text-muted">Use <code>--no-timestamps</code> to omit automatic timestamp columns.</p>
                </div>
            </div>
        </section>

        <!-- ============ DATABASE ============ -->
        <section id="database">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Database (Fluent Query Builder)</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>The query builder provides a fluent, chainable interface for building and running SQL queries. Get an instance via <code>$this-&gt;loadDatabase()-&gt;table('name')</code> or directly from a model with <code>Model::where(...)</code>.</p>

                    <h5>Configuration</h5>
                    <p>Edit <code>system/config/database.php</code>:</p>
                    <pre>return [
    'default' => [
        'driver'   => 'mysql',      // mysql, pgsql, sqlite
        'host'     => '127.0.0.1',
        'port'     => 3306,
        'username' => 'root',
        'password' => '',
        'database' => 'my_app',
        'charset'  => 'utf8',
    ],

    // SQLite (no host/port needed)
    'sqlite' => [
        'driver'   => 'sqlite',
        'database' => __DIR__ . '/../../site/database.sqlite',
    ],

    // PostgreSQL
    'pgsql' => [
        'driver'   => 'pgsql',
        'host'     => '127.0.0.1',
        'port'     => 5432,
        'username' => 'root',
        'password' => '',
        'database' => 'my_app',
    ],
];</pre>

                    <h5>Supported drivers</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-success">mysql</span> MySQL via PDO
                        <span class="badge bg-primary ms-1">pgsql</span> PostgreSQL via PDO
                        <span class="badge bg-secondary ms-1">sqlite</span> SQLite via PDO
                    </div>

                    <h5 class="mt-3">Basic queries</h5>
                    <pre>$db = $this-&gt;loadDatabase();

// Get all rows
$users = $db-&gt;table('users')-&gt;get();

// Select specific columns
$users = $db-&gt;table('users')-&gt;select('id', 'name', 'email')-&gt;get();

// Get a single row by primary key
$user = $db-&gt;table('users')-&gt;find(1);

// Get the first matching row
$user = $db-&gt;table('users')-&gt;where('email', 'alice@test.com')-&gt;first();</pre>

                    <h5>Where clauses</h5>
                    <pre>// Basic where (assumes '=' operator)
$db-&gt;table('users')-&gt;where('is_active', 1)-&gt;get();

// Custom operator
$db-&gt;table('users')-&gt;where('age', '&gt;=', 18)-&gt;get();
$db-&gt;table('users')-&gt;where('name', 'LIKE', '%alice%')-&gt;get();

// Multiple wheres (AND)
$db-&gt;table('users')-&gt;where('is_active', 1)-&gt;where('age', '&gt;', 21)-&gt;get();

// OR where
$db-&gt;table('users')-&gt;where('role', 'admin')-&gt;orWhere('role', 'moderator')-&gt;get();

// Where IN
$db-&gt;table('users')-&gt;whereIn('id', [1, 2, 3])-&gt;get();

// Where NULL / NOT NULL
$db-&gt;table('users')-&gt;whereNull('deleted_at')-&gt;get();
$db-&gt;table('users')-&gt;whereNotNull('email_verified_at')-&gt;get();</pre>

                    <h5>Ordering, limits &amp; offsets</h5>
                    <pre>$db-&gt;table('users')-&gt;orderBy('name')-&gt;get();
$db-&gt;table('users')-&gt;orderBy('created_at', 'DESC')-&gt;get();

$db-&gt;table('users')-&gt;limit(10)-&gt;get();
$db-&gt;table('users')-&gt;limit(10)-&gt;offset(20)-&gt;get();    // page 3 of 10

// Shorthand
$db-&gt;table('users')-&gt;take(5)-&gt;skip(10)-&gt;get();</pre>

                    <h5>Joins</h5>
                    <pre>$db-&gt;table('users')
   -&gt;join('posts', 'users.id', '=', 'posts.user_id')
   -&gt;select('users.name', 'posts.title')
   -&gt;get();

$db-&gt;table('users')
   -&gt;leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
   -&gt;get();

$db-&gt;table('orders')
   -&gt;rightJoin('payments', 'orders.id', '=', 'payments.order_id')
   -&gt;get();</pre>

                    <h5>Inserts, updates &amp; deletes</h5>
                    <pre>// Insert single row (returns the new ID)
$id = $db-&gt;table('users')-&gt;insert([
    'name' =&gt; 'Alice',
    'email' =&gt; 'alice@example.com',
]);

// Insert multiple rows (returns row count)
$count = $db-&gt;table('users')-&gt;insert([
    ['name' =&gt; 'Bob', 'email' =&gt; 'bob@test.com'],
    ['name' =&gt; 'Carol', 'email' =&gt; 'carol@test.com'],
]);

// Update (returns affected row count)
$affected = $db-&gt;table('users')-&gt;where('id', 1)-&gt;update([
    'name' =&gt; 'Alice Smith',
]);

// Delete (returns affected row count)
$deleted = $db-&gt;table('users')-&gt;where('id', 5)-&gt;delete();

// Truncate table
$db-&gt;table('users')-&gt;truncate();</pre>

                    <h5>Aggregates</h5>
                    <pre>$count = $db-&gt;table('users')-&gt;count();
$total = $db-&gt;table('orders')-&gt;sum('amount');
$avg   = $db-&gt;table('products')-&gt;avg('price');
$min   = $db-&gt;table('products')-&gt;min('price');
$max   = $db-&gt;table('products')-&gt;max('price');
$exists = $db-&gt;table('users')-&gt;where('email', $email)-&gt;exists();</pre>

                    <h5>Group by &amp; having</h5>
                    <pre>$db-&gt;table('orders')
   -&gt;select('user_id', 'SUM(amount) as total')
   -&gt;groupBy('user_id')
   -&gt;having('total', '&gt;', 100)
   -&gt;get();</pre>

                    <h5>Raw queries</h5>
                    <pre>$results = $db-&gt;query('SELECT * FROM users WHERE active = ?', [1]);
$db-&gt;statement('UPDATE users SET last_login = NOW() WHERE id = ?', [$id]);</pre>

                    <h5>Transactions</h5>
                    <pre>$db-&gt;beginTransaction();
try {
    $db-&gt;table('accounts')-&gt;where('id', 1)-&gt;update(['balance' =&gt; 100]);
    $db-&gt;table('accounts')-&gt;where('id', 2)-&gt;update(['balance' =&gt; 200]);
    $db-&gt;commit();
} catch (\Exception $e) {
    $db-&gt;rollBack();
}</pre>

                    <h5>Pagination</h5>
                    <pre>$paginator = $db-&gt;table('users')-&gt;paginate(15);

// In your controller:
$data = [
    'users' =&gt; $paginator-&gt;items(),
    'links' =&gt; $paginator-&gt;links(),    // HTML pagination links
];

// Or with a model:
$paginator = User::paginate(15);

// Pagination methods:
$paginator-&gt;currentPage();   // int
$paginator-&gt;lastPage();      // int
$paginator-&gt;total();         // int
$paginator-&gt;hasPages();      // bool
$paginator-&gt;hasMorePages();  // bool
$paginator-&gt;onFirstPage();   // bool
$paginator-&gt;nextPageUrl();   // string|null
$paginator-&gt;previousPageUrl(); // string|null
$paginator-&gt;toArray();       // all data as array</pre>

                    <h5 class="mt-3">Multiple database connections</h5>
                    <p>Define multiple groups in <code>database.php</code> and reference them by name:</p>
                    <pre>$analytics = $this-&gt;loadDatabase('analytics');
$logs = $this-&gt;loadDatabase('logs');</pre>

                    <h5>Backward compatibility</h5>
                    <p>The old API still works — no need to rewrite existing code:</p>
                    <pre>$db = $this-&gt;loadDatabase();
$users = $db-&gt;get('users');
$user  = $db-&gt;getWhere('users', ['id' =&gt; 1]);
$id    = $db-&gt;insert('users', ['name' =&gt; 'Alice']);
$db-&gt;update('users', ['name' =&gt; 'Bob'], ['id' =&gt; 1]);
$db-&gt;delete('users', ['id' =&gt; 1]);</pre>
                </div>
            </div>
        </section>

        <!-- ============ MIGRATIONS ============ -->
        <section id="migrations">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Migrations</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Migrations are version-controlled schema changes stored in <code>site/migrations/</code>. They let you define and share your database structure with your team.</p>

                    <h5>Creating a migration</h5>
                    <pre>php aura make:migration create_users_table</pre>
                    <p class="text-sm text-muted">Name migrations descriptively with <code>snake_case</code>. The prefix <code>create_</code> and suffix <code>_table</code> are detected to pre-fill the schema.</p>

                    <p>This generates a file like <code>site/migrations/2026_06_01_120000_create_users_table.php</code>:</p>
                    <pre>&lt;?php

use AuraCore\Schema;
use AuraCore\Migration;

class Migration_CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function ($table) {
            $table-&gt;increments('id');
            $table-&gt;string('name', 100);
            $table-&gt;string('email')-&gt;unique();
            $table-&gt;string('password');
            $table-&gt;boolean('is_active')-&gt;defaultValue(1);
            $table-&gt;timestamps();     // created_at, updated_at
            $table-&gt;softDeletes();    // deleted_at
        });
    }

    public function down()
    {
        Schema::drop('users');
    }
}</pre>

                    <h5>Running migrations</h5>
                    <pre>php aura migrate</pre>
                    <p>This runs all pending migrations in order. A <code>migrations</code> table tracks what has been applied.</p>

                    <h5>Rolling back</h5>
                    <pre>php aura migrate:rollback</pre>
                    <p>Rolls back the last batch of migrations by calling each <code>down()</code> method.</p>

                    <h5>Available column types</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered data-table-sm">
                            <thead><tr><th>Method</th><th>SQL Type</th></tr></thead>
                            <tbody>
                                <tr><td><code>$table-&gt;increments('id')</code></td><td>INT UNSIGNED AUTO_INCREMENT (primary key)</td></tr>
                                <tr><td><code>$table-&gt;bigIncrements('id')</code></td><td>BIGINT UNSIGNED AUTO_INCREMENT</td></tr>
                                <tr><td><code>$table-&gt;string('col', 255)</code></td><td>VARCHAR</td></tr>
                                <tr><td><code>$table-&gt;char('col', 255)</code></td><td>CHAR</td></tr>
                                <tr><td><code>$table-&gt;text('col')</code></td><td>TEXT</td></tr>
                                <tr><td><code>$table-&gt;mediumText('col')</code></td><td>MEDIUMTEXT</td></tr>
                                <tr><td><code>$table-&gt;longText('col')</code></td><td>LONGTEXT</td></tr>
                                <tr><td><code>$table-&gt;integer('col')</code></td><td>INT</td></tr>
                                <tr><td><code>$table-&gt;bigInteger('col')</code></td><td>BIGINT</td></tr>
                                <tr><td><code>$table-&gt;tinyInteger('col')</code></td><td>TINYINT</td></tr>
                                <tr><td><code>$table-&gt;boolean('col')</code></td><td>TINYINT(1)</td></tr>
                                <tr><td><code>$table-&gt;decimal('col', 8, 2)</code></td><td>DECIMAL</td></tr>
                                <tr><td><code>$table-&gt;float('col', 8, 2)</code></td><td>FLOAT</td></tr>
                                <tr><td><code>$table-&gt;date('col')</code></td><td>DATE</td></tr>
                                <tr><td><code>$table-&gt;dateTime('col')</code></td><td>DATETIME</td></tr>
                                <tr><td><code>$table-&gt;timestamp('col')</code></td><td>TIMESTAMP</td></tr>
                                <tr><td><code>$table-&gt;json('col')</code></td><td>JSON</td></tr>
                                <tr><td><code>$table-&gt;enum('col', ['a', 'b'])</code></td><td>ENUM</td></tr>
                                <tr><td><code>$table-&gt;binary('col')</code></td><td>BLOB</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3">Column modifiers</h5>
                    <pre>$table-&gt;string('email')-&gt;nullable();
$table-&gt;string('email')-&gt;nullable(false);
$table-&gt;integer('views')-&gt;defaultValue(0);
$table-&gt;integer('id')-&gt;unsigned();
$table-&gt;string('col')-&gt;after('id');
$table-&gt;string('col')-&gt;comment('User display name');
$table-&gt;string('email')-&gt;unique('optional_index_name');
$table-&gt;string('name')-&gt;index('idx_name');</pre>

                    <h5>Foreign keys</h5>
                    <pre>Schema::create('posts', function ($table) {
    $table-&gt;increments('id');
    $table-&gt;string('title');
    $table-&gt;integer('user_id')-&gt;unsigned();
    $table-&gt;text('body');
    $table-&gt;timestamps();

    $table-&gt;foreign('user_id')
          -&gt;references('id')
          -&gt;on('users')
          -&gt;onDelete('cascade')
          -&gt;onUpdate('cascade');
});</pre>

                    <h5>Modifying existing tables</h5>
                    <pre>php aura make:migration add_phone_to_users_table</pre>
                    <pre>public function up()
{
    Schema::table('users', function ($table) {
        $table-&gt;string('phone', 20)-&gt;nullable()-&gt;after('email');
        $table-&gt;string('avatar')-&gt;nullable();
    });
}

public function down()
{
    Schema::table('users', function ($table) {
        $table-&gt;dropColumn('phone');
        $table-&gt;dropColumn('avatar');
    });
}</pre>

                    <h5>Dropping tables</h5>
                    <pre>Schema::drop('users');
Schema::dropIfExists('users');</pre>

                    <h5>Checking table/column existence</h5>
                    <pre>if (Schema::hasTable('users')) { ... }
if (Schema::hasColumn('users', 'email')) { ... }</pre>
                </div>
            </div>
        </section>

        <!-- ============ SEEDING ============ -->
        <section id="seeding">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Database Seeding</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Seeders populate your database with test or default data. They live in <code>site/seeders/</code>.</p>

                    <h5>Creating a seeder</h5>
                    <pre>php aura make:seeder User</pre>

                    <p>This generates <code>site/seeders/user_seeder.php</code>:</p>
                    <pre>&lt;?php

use AuraCore\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $this-&gt;db-&gt;table('users')-&gt;insert([
            ['name' =&gt; 'Alice', 'email' =&gt; 'alice@example.com'],
            ['name' =&gt; 'Bob',   'email' =&gt; 'bob@example.com'],
            ['name' =&gt; 'Carol', 'email' =&gt; 'carol@example.com'],
        ]);
    }
}</pre>

                    <h5>Running seeders</h5>
                    <pre>php aura db:seed</pre>
                    <p>Runs all seeders in <code>site/seeders/</code> in alphabetical order.</p>

                    <h5>Calling other seeders</h5>
                    <pre>class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this-&gt;call(UserSeeder::class);
        $this-&gt;call(PostSeeder::class);
        $this-&gt;call(TagSeeder::class);
    }
}</pre>
                </div>
            </div>
        </section>

        <!-- ============ VALIDATION ============ -->
        <section id="validation">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Validation</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Validate incoming request data with the built-in <code>Validator</code> class. Supports common rules like required, email, min, max, unique, and more.</p>

                    <h5>Basic usage</h5>
                    <pre>use AuraCore\Validator;

$validator = new Validator();
$validation = $validator->validate($_POST, [
    'name'  => 'required|string|max:255',
    'email' => 'required|email|unique:users,email',
    'age'   => 'required|numeric|min:18|max:120',
    'terms' => 'required',
]);

if ($validation->fails()) {
    $errors = $validation->errors();
    // $errors['name'] = ['The name field is required.']
}</pre>

                    <h5>In a controller</h5>
                    <pre>public function store()
{
    $validation = $this->validate(request()->all(), [
        'title' => 'required|string|max:255',
        'body'  => 'required|string',
        'email' => 'required|email',
    ]);

    if ($validation->fails()) {
        return $this->response()->back();
    }

    // Save the data...
}</pre>

                    <h5>Available rules</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered data-table-sm">
                            <thead><tr><th>Rule</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>required</code></td><td>Field must not be empty</td></tr>
                                <tr><td><code>email</code></td><td>Must be a valid email address</td></tr>
                                <tr><td><code>min:N</code></td><td>Minimum length (string) / value (numeric) / count (array)</td></tr>
                                <tr><td><code>max:N</code></td><td>Maximum length / value / count</td></tr>
                                <tr><td><code>numeric</code></td><td>Must be a number</td></tr>
                                <tr><td><code>integer</code></td><td>Must be an integer</td></tr>
                                <tr><td><code>string</code></td><td>Must be a string</td></tr>
                                <tr><td><code>boolean</code></td><td>Must be true/false/0/1</td></tr>
                                <tr><td><code>confirmed</code></td><td>Field must match <code>field_confirmation</code></td></tr>
                                <tr><td><code>same:N</code></td><td>Must match field <code>N</code></td></tr>
                                <tr><td><code>url</code></td><td>Must be a valid URL</td></tr>
                                <tr><td><code>alpha</code></td><td>Must contain only letters</td></tr>
                                <tr><td><code>alpha_num</code></td><td>Must contain only letters and numbers</td></tr>
                                <tr><td><code>alpha_dash</code></td><td>Must contain only letters, numbers, dashes, underscores</td></tr>
                                <tr><td><code>date</code></td><td>Must be a valid date</td></tr>
                                <tr><td><code>after:DATE</code></td><td>Must be after the given date</td></tr>
                                <tr><td><code>before:DATE</code></td><td>Must be before the given date</td></tr>
                                <tr><td><code>size:N</code></td><td>Must be exactly N characters/values</td></tr>
                                <tr><td><code>between:N,M</code></td><td>Must be between N and M</td></tr>
                                <tr><td><code>unique:table,column,ignoreId,ignoreColumn</code></td><td>Value must be unique in database table</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <h5 class="mt-3">Custom error messages</h5>
                    <pre>$validation = $validator->validate($data, [
        'email' => 'required|email',
    ], [
        'email.required' => 'We need your email address!',
        'email.email'    => 'That does not look like an email...',
    ]);</pre>
                </div>
            </div>
        </section>

        <!-- ============ MIDDLEWARE ============ -->
        <section id="middleware">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Middleware</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>Middleware sits between a request and your controller. Use it for authentication checks, logging, CORS headers, request modification, and more.</p>

                    <h5>Creating middleware</h5>
                    <pre>php aura make:middleware Auth</pre>
                    <p>Generated in <code>site/middleware/</code>:</p>
                    <pre>&lt;?php

namespace SiteMiddleware;

class Auth
{
    public function handle($request, $next)
    {
        // Code before the request reaches the controller
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $response = $next($request);

        // Code after the controller has handled the request
        return $response;
    }
}</pre>

                    <h5>Applying middleware to routes</h5>
                    <pre>// Single route
$router->get('dashboard', 'dashboard@index')->middleware('Auth');

// Route group with middleware
$router->group(['middleware' => ['Auth']], function ($router) {
    $router->get('dashboard', 'dashboard@index');
    $router->get('settings', 'settings@index');
    $router->post('settings', 'settings@update');
});</pre>

                    <p>Middleware classes in <code>site/middleware/</code> are auto-discovered. Reference them by class name (without namespace prefix).</p>
                </div>
            </div>
        </section>

        <!-- ============ AUTHENTICATION ============ -->
        <section id="auth">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Authentication</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>The <code>Auth</code> class provides session-based authentication. It works with any model that has <code>email</code> and <code>password</code> columns.</p>

                    <h5>Basic usage</h5>
                    <pre>use AuraCore\Auth;

$auth = new Auth(User::class);

// Attempt login
if ($auth->attempt($_POST)) {
    // Logged in! Auth::user() returns the User model
    $user = $auth->user();
    header('Location: /dashboard');
} else {
    echo 'Invalid credentials.';
}

// Check if logged in
if ($auth->check()) {
    echo 'Welcome back, ' . $auth->user()->name;
}

// Logout
$auth->logout();</pre>

                    <h5>In a controller</h5>
                    <pre>class AuthController extends Controller
{
    public function login()
    {
        $data = $this->request()->only(['email', 'password']);

        if ($this->auth()->attempt($data)) {
            return $this->response()->redirect('/dashboard');
        }

        return $this->response()->back();
    }

    public function logout()
    {
        $this->auth()->logout();
        return $this->response()->redirect('/');
    }

    public function profile()
    {
        if ($this->auth()->guest()) {
            return $this->response()->redirect('/login');
        }

        $user = $this->auth()->user();
        $this->loadView('profile', ['user' => $user]);
    }
}</pre>

                    <h5>Auth methods</h5>
                    <div class="overflow-auto">
                        <table class="data-table data-table-bordered data-table-sm">
                            <thead><tr><th>Method</th><th>Description</th></tr></thead>
                            <tbody>
                                <tr><td><code>$auth->attempt($credentials)</code></td><td>Attempt login with email/password. Returns bool.</td></tr>
                                <tr><td><code>$auth->login($user)</code></td><td>Manually log in a user instance.</td></tr>
                                <tr><td><code>$auth->logout()</code></td><td>Log out and regenerate session.</td></tr>
                                <tr><td><code>$auth->user()</code></td><td>Get the authenticated user model (or null).</td></tr>
                                <tr><td><code>$auth->check()</code></td><td>Returns true if a user is logged in.</td></tr>
                                <tr><td><code>$auth->guest()</code></td><td>Returns true if no user is logged in.</td></tr>
                                <tr><td><code>$auth->id()</code></td><td>Get the authenticated user's ID.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-sm text-muted mt-3">Note: passwords are verified with <code>password_verify()</code>. Make sure to hash them with <code>password_hash()</code> when creating users.</p>
                </div>
            </div>
        </section>

        <!-- ============ ERROR HANDLING ============ -->
        <section id="error-handling">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Error Handling</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>AuraPHP includes a built-in error handler that converts PHP errors into exceptions and renders a pretty HTML page with code context.</p>

                    <h5>Features</h5>
                    <ul>
                        <li>Converts all PHP errors into <code>ErrorException</code></li>
                        <li>Pretty HTML error page with source code highlighting</li>
                        <li>Full stack trace with argument inspection</li>
                        <li>CLI-friendly formatted output for command-line usage</li>
                        <li>Shutdown handler catches fatal errors</li>
                    </ul>

                    <h5>Error page shows</h5>
                    <ul>
                        <li>Error class and message</li>
                        <li>File and line number</li>
                        <li>Source code snippet with the error line highlighted</li>
                        <li>Full stack trace with each call's arguments</li>
                    </ul>

                    <p>The error handler is registered automatically in <code>index.php</code>. In production, set <code>false</code> to show a simple 500 page instead.</p>
                </div>
            </div>
        </section>

        <!-- ============ EVENTS ============ -->
        <section id="events">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Events</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>The event system provides a simple pub/sub pattern for decoupled communication between components.</p>

                    <h5>Listening to events</h5>
                    <pre>use AuraCore\Event;

// Register a listener
Event::listen('user.registered', function ($user) {
    // Send welcome email
    // Log to analytics
});

Event::listen('user.registered', function ($user) {
    // Award signup bonus
});</pre>

                    <h5>Dispatching events</h5>
                    <pre>Event::dispatch('user.registered', [$user]);

// Multiple arguments
Event::dispatch('order.shipped', [$order, $trackingNumber]);</pre>

                    <h5>Creating event classes</h5>
                    <pre>php aura make:event UserRegistered</pre>
                    <p>Events live in <code>site/events/</code>. They are plain PHP classes that hold data — the event system works with any class or data.</p>

                    <h5>Creating listeners</h5>
                    <pre>php aura make:listener SendWelcomeEmail</pre>
                    <p>Listeners live in <code>site/listeners/</code>. Wire them up in your code with <code>Event::listen()</code>.</p>
                </div>
            </div>
        </section>

        <!-- ============ LOGGING ============ -->
        <section id="logging">
            <h2 class="fw-bold mt-5 mb-3 pb-2 border-bottom">Logging</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <p>The <code>Logger</code> class writes structured log entries to daily files in <code>storage/logs/</code>.</p>

                    <h5>Basic usage</h5>
                    <pre>use AuraCore\Logger;

$logger = new Logger();

$logger->info('User logged in', ['user_id' => 123]);
$logger->error('Database connection failed', ['host' => $host]);
$logger->warning('Disk space low', ['percent' => '92%']);
$logger->critical('Application crashed');
$logger->debug('SQL query executed', ['sql' => $sql]);</pre>

                    <h5>Log levels</h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge bg-secondary">DEBUG</span>
                        <span class="badge bg-info">INFO</span>
                        <span class="badge bg-primary">NOTICE</span>
                        <span class="badge bg-warning text-dark">WARNING</span>
                        <span class="badge bg-danger">ERROR</span>
                        <span class="badge bg-dark">CRITICAL</span>
                    </div>

                    <h5 class="mt-3">Log output</h5>
                    <pre>[2026-06-01 14:30:00] INFO: User logged in {"user_id":123}
[2026-06-01 14:31:15] ERROR: Database connection failed {"host":"localhost"}</pre>

                    <p>Logs are stored in <code>storage/logs/YYYY-MM-DD.log</code>. Set minimum level via the constructor: <code>new Logger(null, 'WARNING')</code> to skip DEBUG and INFO messages.</p>
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
                                <tr><td><code>php aura make:model</code></td><td>Generate an ActiveRecord model</td><td><code>php aura make:model User</code></td></tr>
                                <tr><td><code>php aura make:migration</code></td><td>Generate a database migration</td><td><code>php aura make:migration create_users_table</code></td></tr>
                                <tr><td><code>php aura migrate</code></td><td>Run all pending migrations</td><td><code>php aura migrate</code></td></tr>
                                <tr><td><code>php aura migrate:rollback</code></td><td>Rollback the last migration batch</td><td><code>php aura migrate:rollback</code></td></tr>
                                <tr><td><code>php aura make:seeder</code></td><td>Generate a database seeder</td><td><code>php aura make:seeder User</code></td></tr>
                                <tr><td><code>php aura db:seed</code></td><td>Run all seeders</td><td><code>php aura db:seed</code></td></tr>
                                <tr><td><code>php aura make:view</code></td><td>Generate a view</td><td><code>php aura make:view about</code></td></tr>
                                <tr><td><code>php aura make:middleware</code></td><td>Generate a middleware class</td><td><code>php aura make:middleware Auth</code></td></tr>
                                <tr><td><code>php aura make:event</code></td><td>Generate an event class</td><td><code>php aura make:event UserRegistered</code></td></tr>
                                <tr><td><code>php aura make:listener</code></td><td>Generate an event listener</td><td><code>php aura make:listener SendEmail</code></td></tr>
                                <tr><td><code>php aura make:request</code></td><td>Generate a form request class</td><td><code>php aura make:request StoreUser</code></td></tr>
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
                    <p>Generates an ActiveRecord model that maps to a database table:</p>
                    <pre>php aura make:model User
# Creates site/models/user.php with ActiveRecord methods:
# User::all(), User::find(1), User::where(...),
# $user-&gt;save(), $user-&gt;delete(), etc.</pre>

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
                            <div class="text-sm"><code>$db = $this-&gt;loadDatabase();<br>$db-&gt;table('users')-&gt;where('x', 1)-&gt;get();</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Find a model</h5>
                            <div class="text-sm"><code>$user = User::find(1);<br>$users = User::where('active', 1)-&gt;get();</code></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5>Run migrations</h5>
                            <div class="text-sm"><code>php aura migrate</code></div>
                        </div>
                    </div>
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
<script>
(function(){var t=localStorage.getItem("theme"),b=document.body;if(t&&t!=="theme-light"){b.classList.replace("theme-light","theme-dark");var i=document.getElementById("themeToggle");i&&(i.innerHTML="&#9788;")
var s=document.getElementById("hljs-theme");s&&(s.href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css")}})();function toggleTheme(){var e=document.body,i=document.getElementById("themeToggle");e.classList.toggle("theme-dark"),e.classList.toggle("theme-light");var n=e.classList.contains("theme-dark");localStorage.setItem("theme",n?"theme-dark":"theme-light"),i&&(i.innerHTML=n?"&#9788;":"&#9790;")
var s=document.getElementById("hljs-theme");s&&(s.href=n?"https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css":"https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github.min.css")}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
<script>
(function() {
    function detectLang(text) {
        if (/&lt;\?php|\$this-&gt;|\$db-&gt;|function\s+\w+|namespace\s+|use\s+(AuraCore|SiteControllers)/.test(text)) return 'php';
        if (/&lt;!DOCTYPE|<html|<head|<body|<div|<a\s|<pre/.test(text)) return 'xml';
        if (/(CREATE|SELECT|INSERT|UPDATE|DELETE|ALTER|DROP|TABLE|WHERE|FROM)\s/i.test(text)) return 'sql';
        if (/^(composer|git|php\s(aura|artisan)|npm|cd\s)/.test(text.trim())) return 'bash';
        if (/(function\s+\w+\s*\(|=>|const\s+\w+|require|module\.)/.test(text)) return 'javascript';
        return 'plaintext';
    }
    document.querySelectorAll('pre').forEach(function(el) {
        if (!el.querySelector('code')) {
            var lang = el.getAttribute('data-lang') || detectLang(el.textContent);
            el.setAttribute('data-lang', lang);
            var code = document.createElement('code');
            code.className = 'language-' + lang;
            code.innerHTML = el.innerHTML;
            el.innerHTML = '';
            el.appendChild(code);
        }
    });
    hljs.highlightAll();
})();
</script>
<?php ownstrap_js(); ?>
</body>
</html>