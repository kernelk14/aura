# AuraPHP

**AuraPHP** is a lightweight PHP MVC framework designed for rapid web application development. It ships with **OwnStrap**, a custom CSS/JS framework providing 300+ utility classes and 10+ interactive components — no external dependencies required.

- **Size**: ~150KB total (framework + CSS + JS + fonts)
- **PHP**: 7.4+
- **License**: MIT

---

## Installation

### Via Composer

```bash
composer create-project kernelk14/aura my-app
cd my-app
php aura serve
```

### Manual

Clone the repository and point your web server to the project root:

```bash
git clone https://github.com/kernelk14/aura.git
cd auraphp
php aura serve
```

### Requirements

| Requirement | Version |
|-------------|---------|
| PHP | >= 7.4 |
| Web Server | Apache (mod_rewrite), Nginx, or PHP built-in |
| Database (optional) | MySQL, PostgreSQL, or SQLite |
| Composer (optional) | For dependency management |

---

## Quick Start

### 1. Start the dev server

```bash
php aura serve
# Opens at http://127.0.0.1:8080
```

### 2. Configure

Edit `system/config/config.php`:
```php
return [
    'base_url' => 'http://localhost:8080',
];
```

### 3. Define a route

```php
// system/config/routes.php
$router->get('hello', 'hello@index');
```

### 4. Create a controller

```php
// site/controllers/hello.php
namespace SiteControllers;

use AuraCore\Controller;

class Hello extends Controller
{
    public function index()
    {
        $this->loadView('hello', [
            'title' => 'Hello World',
        ]);
    }
}
```

### 5. Create a view

```php
<div class="container mt-5 text-center">
    <h1 class="text-gradient-success"><?= htmlspecialchars($title) ?></h1>
    <p class="lead">Welcome to AuraPHP.</p>
</div>
```

Views are pure content — no HTML shell needed. The template layout (`DOCTYPE`, `<html>`, `<head>`, `<body>`, navbar, JS) wraps around your view automatically.

---

## The `aura` CLI Tool

AuraPHP includes a powerful command-line tool inspired by Laravel Artisan and Spiral Spark.

```bash
php aura list
```

| Command | Description |
|---------|-------------|
| `php aura serve` | Start the PHP development server |
| `php aura route:list` | Display all registered routes |
| `php aura make:controller <name>` | Generate a controller |
| `php aura make:controller <name> --resource` | Generate a resource controller |
| `php aura make:model <name>` | Generate a model |
| `php aura make:view <name>` | Generate a view |
| `php aura key:generate` | Generate an application encryption key |

### Examples

```bash
# Start development server on custom port
php aura serve --port=3000 --host=0.0.0.0

# Generate a resource controller with 7 REST methods
php aura make:controller Product --resource

# Generate a model with CRUD methods
php aura make:model User

# List all routes with methods and handlers
php aura route:list
```

---

## Project Structure

```
my-app/
├── aura                         # CLI tool
├── public/
│   ├── css/
│   │   └── ownstrap.css         # Main CSS framework (~2609 lines)
│   ├── js/
│   │   └── ownstrap.js          # JavaScript library (~1154 lines)
│   └── fonts/                   # Inter & Fira Sans fonts
├── site/
│   ├── controllers/             # Application controllers
│   ├── models/                  # Application models
│   ├── templates/               # Reusable template partials
│   │   ├── sidebar-framework.php
│   │   └── sidebar-ownstrap.php
│   └── views/                   # Application views
├── system/
│   ├── core/
│   │   ├── base.php             # Base class
│   │   ├── content-wrapper.php    # Base HTML layout
│   │   ├── controller.php       # Base controller
│   │   ├── model.php            # Base model
│   │   ├── router.php           # Router
│   │   └── database.php         # Database + QueryBuilder
│   ├── config/
│   │   ├── config.php           # Application config
│   │   ├── database.php         # Database connection config
│   │   └── routes.php           # Route definitions
│   └── helpers/
│       └── url_helper.php       # Global helper functions
├── vendor/                      # Composer dependencies
├── composer.json
├── index.php                    # Entry point
├── .htaccess                    # Apache rewrite rules
└── .env                         # Environment variables
```

---

## Routing

Routes are defined in `system/config/routes.php`. The router supports GET, POST, PUT, DELETE, and wildcard (`any`) methods.

### Basic routes

```php
$router->get('/', 'welcome@index');
$router->get('about', 'page@about');
$router->post('contact', 'contact@store');
$router->put('users/:id', 'user@update');
$router->delete('users/:id', 'user@destroy');
$router->any('webhook', 'webhook@handle');
```

### Route parameters

Use `:param` syntax. Parameters are passed as positional arguments to the controller method:

```php
// Route
$router->get('blog/:id/:slug', 'blog@show');

// Controller method receives $id and $slug
class Blog extends Controller {
    public function show($id, $slug) {
        echo "Post #{$id}: {$slug}";
    }
}
```

### Callback handlers

You can also use closures directly:

```php
$router->get('api/time', function() {
    echo json_encode(['time' => date('Y-m-d H:i:s')]);
});
```

### Route listing

```bash
php aura route:list
```

Output:

```
Method  Path                                    Handler
────────────────────────────────────────────────────────────────────────────────
GET     /                                       welcome@index
GET     /welcome                                welcome@index
GET     /components                             components@index
GET     /demo/user/:id                          demo@user
```

---

## Controllers

Controllers live in `site/controllers/` and extend `AuraCore\Controller`.

### Naming conventions

| Item | Convention | Example |
|------|-----------|---------|
| File name | Lowercase kebab | `user-profile.php` |
| Class name | PascalCase | `UserProfile` |
| Namespace | `SiteControllers` | `SiteControllers\UserProfile` |
| Route handler | `file@method` | `user-profile@index` |

### Example controller

```php
<?php
// site/controllers/user-profile.php

namespace SiteControllers;

use AuraCore\Controller;

class UserProfile extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'User Profiles',
            'users' => ['Alice', 'Bob', 'Carol'],
        ];
        $this->loadView('user-profile', $data);
    }

    public function show($id)
    {
        $db = $this->loadDatabase();
        $user = $db->getWhere('users', ['id' => $id]);

        $this->loadView('user-profile/show', [
            'title' => 'User Details',
            'user' => $user,
        ]);
    }
}
```

### Controller methods

| Method | Description |
|--------|-------------|
| `$this->loadView('name', $data)` | Load a view with extracted data |
| `$this->loadModel('user_model')` | Load a model instance |
| `$this->loadDatabase($group)` | Load a database connection |
| `$this->redirect('url')` | Redirect to a URL |
| `$this->config('item')` | Get a configuration value |

### Generating controllers

```bash
# Simple controller
php aura make:controller UserProfile

# Resource controller (7 RESTful methods)
php aura make:controller Product --resource
```

The `--resource` flag generates: `index()`, `create()`, `store()`, `show($id)`, `edit($id)`, `update($id)`, `destroy($id)`.

---

## Views

Views are plain PHP files in `site/views/`. They receive data as extracted variables.

### Template pattern

Views are pure content — no HTML shell needed. The layout (`template.php`) automatically wraps your view:

```php
<div class="container mt-5">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
</div>
```

The layout provides the DOCTYPE, `<html>`, `<head>` (with `ownstrap_css()`), `<body>` with navbar, and `ownstrap_js()` before the closing `</body>` tag. Your view only needs the middle content.

To skip the layout (e.g. for standalone pages), pass `false` as the third argument:
```php
$this->loadView('welcome', $data, false);
```

### Passing data

Controllers pass data as an associative array:

```php
// Controller
$this->loadView('user-profile', [
    'title' => 'User Profile',
    'user' => ['name' => 'Alice', 'email' => 'alice@example.com'],
]);
```

The view receives `$title` and `$user` as variables:

```php
<h1><?= htmlspecialchars($title) ?></h1>
<p><?= htmlspecialchars($user['name']) ?></p>
```

### Helper functions available in views

| Function | Description |
|----------|-------------|
| `<?php ownstrap_css() ?>` | Outputs CSS `<link>` tags |
| `<?php ownstrap_js() ?>` | Outputs JS `<script>` tag |
| `<?= site_url('path') ?>` | Full URL to a path |
| `<?= base_url('path') ?>` | Base URL with path |
| `<?php template('name') ?>` | Include a template partial from `site/templates/` |

### Reusable template partials

Place shared view fragments in `site/templates/` and include them with the `template()` helper:

```php
<?php template('sidebar-framework') ?>   <!-- includes site/templates/sidebar-framework.php -->
```

This keeps views DRY — useful for sidebars, footers, navs, or any repeated markup.

---

## Models

Models go in `site/models/` and extend `AuraCore\Model`.

```php
<?php
// site/models/user_model.php

use AuraCore\Model;

class User_Model extends Model
{
    private $table = 'users';

    public function getAll()
    {
        $db = $this->loadDatabase();
        return $db->get($this->table);
    }

    public function getById($id)
    {
        $db = $this->loadDatabase();
        return $db->getWhere($this->table, ['id' => $id]);
    }

    public function create($data)
    {
        $db = $this->loadDatabase();
        return $db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        $db = $this->loadDatabase();
        return $db->update($this->table, $data, ['id' => $id]);
    }

    public function delete($id)
    {
        $db = $this->loadDatabase();
        return $db->delete($this->table, ['id' => $id]);
    }
}
```

Usage in a controller:

```php
$userModel = $this->loadModel('user_model');
$users = $userModel->getAll();
```

### Generating models

```bash
php aura make:model User
```

---

## Database

### Configuration

Edit `system/config/database.php`:

```php
return [
    'default' => [
        'driver'   => 'pdo_mysql',   // mysqli, pdo_mysql, pdo_pgsql, pdo_sqlite
        'host'     => 'localhost',
        'username' => 'root',
        'password' => 'secret',
        'database' => 'my_app',
        'charset'  => 'utf8',
    ],
];
```

### Usage

```php
$db = $this->loadDatabase();

// Get all rows
$users = $db->get('users');

// Get with WHERE
$user = $db->getWhere('users', ['id' => 1]);

// Insert
$id = $db->insert('users', ['name' => 'Alice', 'email' => 'alice@example.com']);

// Update
$db->update('users', ['name' => 'Bob'], ['id' => 1]);

// Delete
$db->delete('users', ['id' => 1]);

// Raw query
$results = $db->query('SELECT * FROM users WHERE active = 1');
```

### Supported drivers

| Driver | Value | Notes |
|--------|-------|-------|
| MySQLi | `mysqli` | MySQL via mysqli extension |
| PDO MySQL | `pdo_mysql` | MySQL via PDO |
| PDO PostgreSQL | `pdo_pgsql` | PostgreSQL via PDO |
| PDO SQLite | `pdo_sqlite` | SQLite file database |

---

## OwnStrap CSS Framework

OwnStrap is a lightweight CSS/JS framework bundled with AuraPHP. It provides 300+ utility classes and 10+ interactive components with no external dependencies.

### Quick reference

```html
<?php ownstrap_css(); ?>   <!-- In <head> -->
<?php ownstrap_js(); ?>    <!-- Before </body> -->
```

### Themes

```html
<body class="theme-dark">   <!-- Dark background, light text -->
<body class="theme-light">  <!-- Light background, dark text -->
```

### Grid system

```html
<div class="container">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-4">
            <!-- Responsive column -->
        </div>
    </div>
</div>
```

| Class | Description |
|-------|-------------|
| `.container` | Max 1200px centered container |
| `.container-fluid` | Full width container |
| `.row` | Flexbox row |
| `.col` | Equal-width column |
| `.col-1` through `.col-12` | Fixed-width columns |
| `.col-sm/md/lg/xl-*` | Responsive breakpoint columns |
| `.col-auto` | Auto-width column |

### Colors

| Role | Hex | Class Prefix |
|------|-----|-------------|
| Success/Green | `#32de84` | `success`, `green` |
| Warning/Orange | `#FFB72C` | `warning`, `orange` |
| Danger/Red | `#FF0800` | `danger`, `red` |
| Info/Blue | `#00A8E8` | `info`, `blue` |
| Purple | `#a855f7` | `purple` |
| Pink | `#ec4899` | `pink` |
| Cyan | `#06b6d4` | `cyan` |
| Indigo | `#6366f1` | `indigo` |
| Teal | `#14b8a6` | `teal` |

Usage: `.text-{color}`, `.bg-{color}`, `.border-{color}`, `.btn-{color}`, `.badge-{color}`, `.alert-{color}`, `.btn-outline-{color}`

Gradients: `.bg-gradient-{color}`, `.text-gradient-{color}`

### Components

| Component | Classes | JS Required? |
|-----------|---------|-------------|
| Buttons | `.btn .btn-{color} .btn-{sm/lg} .btn-outline-{color} .btn-group .btn-group-vertical` | No |
| Cards | `.card .card-body .card-header .card-footer .card-dark .card-title .card-text` | No |
| Badges | `.badge .badge-{color} .rounded-pill` | No |
| Alerts | `.alert .alert-{color}` | No |
| Modals | `.modal .modal-content .modal-header .modal-body .modal-close` | Yes |
| Tabs | `.tabs .tab-button .tab-content` | Yes |
| Accordion | `.accordion .accordion-item .accordion-header .accordion-body` | Yes |
| Dropdowns | `.dropdown .dropdown-toggle .dropdown-menu .dropdown-item .dropdown-divider` | Yes |
| Toasts | `.toast .toast-{color} .toast-close` | Yes |
| Collapse | `.collapse .collapse.show` | Yes |
| Carousel | `.carousel .carousel-item .carousel-control-prev/next .carousel-indicators` | Yes |
| Tooltips | `.tooltip` (via `data-tooltip` attribute) | Yes |
| Progress | `.progress .progress-bar .progress-bar-striped .progress-bar-animated` | No |
| Spinners | `.spinner .spinner-grow .spinner-{color} .spinner-sm` | No |
| Pagination | `.pagination .page-link .page-link.disabled .page-link.active` | No |
| Breadcrumbs | `.breadcrumb .breadcrumb-item .breadcrumb-item.active` | No |
| Navbar | `.navbar .navbar-brand .navbar-nav .nav-link .navbar-toggler` | No |
| List groups | `.list-group .list-group-item .list-group-item.active .list-group-flush` | No |
| Input groups | `.input-group .input-group-text .input-control` | No |
| Shadows | `.shadow-sm .shadow .shadow-lg .shadow-none` | No |

### Typography

```html
<h1> through <h6>
<p class="lead">Lead paragraph</p>
<p class="small">Small text</p>
<p class="text-xs/sm/base/lg/xl/2xl/3xl/4xl">Font sizes</p>
<p class="fw-light/normal/semibold/bold/bolder">Font weights</p>
<p class="text-start/center/end">Text alignment</p>
<p class="text-lowercase/uppercase/capitalize">Text transform</p>
<p class="text-truncate">Truncated text</p>
<p class="font-monospace">Monospace font</p>
```

### Spacing

```html
<div class="m-1 m-2 m-3 m-4 m-5">Margin: 0.5rem to 3rem</div>
<div class="mt-1 mb-2 ms-3 me-4">Directional margins</div>
<div class="mx-auto">Center horizontally</div>
<div class="my-3">Vertical margin shorthand</div>
<div class="p-1 p-2 p-3 p-4">Padding: 0.5rem to 2rem</div>
<div class="pt-1 pb-2 px-3 py-4">Directional padding</div>
<div class="gap-1 gap-2 gap-3 gap-4">Flex/grid gaps</div>
```

### Display & Visibility

```html
<div class="d-none/block/inline/inline-block/flex/grid">Display utilities</div>
<div class="d-sm/md/lg/xl-*">Responsive display</div>
<div class="d-print-none/block/flex">Print display</div>
<div class="visible/invisible">Visibility</div>
<div class="visually-hidden">Screen reader only</div>
```

### Sizing

```html
<div class="w-25 w-50 w-75 w-100 w-auto">Width percentages</div>
<div class="h-25 h-50 h-75 h-100 h-auto">Height percentages</div>
<div class="min-h-screen">Full viewport height</div>
<div class="h-minus-top-5">calc(100vh - 5rem) — sidebar height below navbar</div>
<div class="mw-100 vw-100 min-vw-100">Max/viewport width</div>
```

### Glass & Theming Utilities

```html
<div class="backdrop-blur">Blurred background filter</div>
<div class="bg-glass">Semi-transparent glass background</div>
<div class="bg-subtle">Subtle background shade</div>
<div class="bg-card">Card background color</div>
```

The `theme-dark` class on `<body>` or any container applies dark mode overrides to all components — cards, tables, navbars, dropdowns, alerts, badges, code blocks, and inputs.

A theme toggle is included in the base layout (`system/core/content-wrapper.php`) with localStorage persistence:

```html
<button class="btn btn-sm btn-outline-info ms-2" onclick="toggleTheme()">🌙</button>
```

The page automatically restores the user's last-selected theme on load.

### Borders & Shadows

```html
<div class="border border-0/2/4">Borders</div>
<div class="border-start/end/top/bottom">Directional borders</div>
<div class="border-{color}">Border colors</div>
<div class="rounded rounded-circle rounded-pill">Border radius</div>
<div class="shadow-sm shadow shadow-lg shadow-none">Shadows</div>
```

### Flexbox

```html
<div class="d-flex flex-column flex-wrap">
<div class="align-items-center">
<div class="justify-content-start/center/end/between/around">
```

### Position

```html
<div class="position-relative/absolute/fixed/sticky">
<div class="fixed-top fixed-bottom sticky-top">
<div class="top-0 bottom-0 start-0 end-0 top-50 start-50">
<div class="translate-middle translate-middle-x/y">
```

### JavaScript API

All OwnStrap JavaScript is available through the global `OS` object.

```javascript
// Modals
OS.openModal('myModal');
OS.closeModal('myModal');

// Toasts
OS.success('Operation completed!');
OS.warning('Check your input.');
OS.error('Something went wrong.');
OS.info('Here is some information.');
OS.purple('Purple toast');
OS.pink('Pink toast');
OS.cyan('Cyan toast');
OS.showToast('Custom message', 'success', 5000);

// Tabs (via data attributes)
// <button data-tab="tab1"> triggers OS.switchTab()

// Accordion
OS.toggleAccordionItem('item1');

// Collapse
OS.toggleCollapse('myCollapse');
// <button data-toggle="collapse" data-target="myCollapse">

// Dropdown
OS.toggleDropdown('myDropdown');

// Carousel
OS.nextCarousel('myCarousel');
OS.prevCarousel('myCarousel');
OS.showCarouselItem('myCarousel', 0);

// Tooltips (via data-tooltip attribute)
// <button data-tooltip="Help text">

// Progress
OS.setProgress('progressBar', 75);
OS.animateProgress('progressBar', 0, 100, 3000);

// Form validation
OS.validateForm('myForm');
OS.clearForm('myForm');
OS.getFormData('myForm');

// HTTP requests
await OS.get('/api/users');
await OS.post('/api/users', { name: 'Alice' });
await OS.put('/api/users/1', { name: 'Bob' });
await OS.del('/api/users/1');

// Storage
OS.storage.set('key', { data: 'value' });
OS.storage.get('key');
OS.storage.remove('key');

// Cookies
OS.cookie.set('theme', 'dark', 30);
OS.cookie.get('theme');
OS.cookie.remove('theme');

// Utilities
OS.copyToClipboard('Text to copy');
OS.smoothScroll('section2');
OS.debounce(fn, 300);
OS.throttle(fn, 100);
```

---

## Accessibility

AuraPHP and OwnStrap include built-in accessibility features:

- **Focus trap** in modals (Tab/Shift+Tab cycle)
- **Keyboard navigation** for tabs (Arrow Left/Right)
- **Keyboard navigation** for accordions (Enter/Space)
- **`aria-expanded`** dynamically set on dropdowns, collapse, accordion
- **`aria-haspopup`** on dropdown toggles
- **`:focus-visible`** outline on all focusable elements
- **`prefers-reduced-motion`** disables all animations
- **`visually-hidden`** and `visually-hidden-focusable` for screen readers
- **Print styles** hide navbar, expand link URLs, remove card shadows

---

## Server Configuration

### Apache (.htaccess)

The included `.htaccess` rewrites all non-file requests to `index.php`:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]
```

### Nginx

```nginx
server {
    listen 80;
    server_name my-app.local;
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
}
```

### PHP Built-in Server

```bash
php aura serve
# or directly:
php -S 127.0.0.1:8080 -t .
```

---

## Configuration Reference

### Application config (`system/config/config.php`)

```php
<?php
return [
    'base_url' => 'http://localhost:8080',
    // Add custom config here
];
```

Access in controllers: `$this->config('base_url')`

### Database config (`system/config/database.php`)

```php
<?php
return [
    'default' => [
        'driver'   => 'pdo_mysql',
        'host'     => 'localhost',
        'username' => 'root',
        'password' => '',
        'database' => 'my_app',
        'charset'  => 'utf8',
    ],
    'analytics' => [
        'driver'   => 'pdo_pgsql',
        'host'     => 'db.example.com',
        'username' => 'analytics_user',
        'password' => 'secret',
        'database' => 'analytics_db',
    ],
];
```

Access in controllers: `$this->loadDatabase('default')` or `$this->loadDatabase('analytics')`

---

## Complete Component Reference

### Text Colors

`.text-success`, `.text-warning`, `.text-danger`, `.text-info`, `.text-purple`, `.text-pink`, `.text-cyan`, `.text-indigo`, `.text-teal`, `.text-dark`, `.text-light`, `.text-muted`, `.text-white`, `.text-black`

### Backgrounds

`.bg-primary`, `.bg-secondary`, `.bg-success`, `.bg-warning`, `.bg-danger`, `.bg-info`, `.bg-purple`, `.bg-pink`, `.bg-cyan`, `.bg-indigo`, `.bg-teal`, `.bg-dark`, `.bg-light`, `.bg-transparent`

### Gradients

`.bg-gradient-success`, `.bg-gradient-warning`, `.bg-gradient-danger`, `.bg-gradient-info`, `.bg-gradient-purple`, `.bg-gradient-pink`, `.bg-gradient-indigo`, `.bg-gradient-cyan`, `.bg-gradient-teal`, `.bg-gradient-sunset`, `.bg-gradient-ocean`, `.bg-gradient-forest`, `.bg-gradient-twilight`, `.text-gradient-success`, `.text-gradient-danger`, `.text-gradient-info`, `.text-gradient-purple`, `.text-gradient-rainbow`

### Buttons

`.btn`, `.btn-primary/secondary/success/warning/danger/info/dark/light`, `.btn-outline-*`, `.btn-sm`, `.btn-lg`, `.btn-group`, `.btn-group-vertical`

### Cards

`.card`, `.card-light`, `.card-dark`, `.card-header`, `.card-body`, `.card-footer`, `.card-title`, `.card-text`

### Forms

`.field-group`, `.field-label`, `.input-control`, `.input-control-sm/lg`, `.select-control`, `.field-text`, `.state-valid`, `.state-invalid`, `.check-option`, `.check-input`, `.check-label`, `.input-group`, `.input-group-text`

### Tables

`.data-table`, `.data-table-striped`, `.data-table-bordered`, `.data-table-hover`, `.data-table-success/warning/danger/info/dark/light`, `.responsive-table`

### Badges

`.badge`, `.badge-success/warning/danger/info/dark/light`, `.badge-pulse`, `.rounded-pill`

### Alerts

`.alert`, `.alert-success/warning/danger/info`

### List Groups

`.list-group`, `.list-group-item`, `.list-group-item.active`, `.list-group-flush`

### Navigation

`.navbar`, `.navbar-light`, `.navbar-brand`, `.nav`, `.nav-link`, `.navbar-toggler`, `.navbar-collapse`, `.pagination`, `.page-link`, `.breadcrumb`, `.breadcrumb-item`

---

## Environment File

AuraPHP supports `.env` files for environment-specific configuration:

```bash
php aura key:generate
```

This creates `.env` with an `APP_KEY`:
```
APP_KEY=0123456789abcdef0123456789abcdef
```

---

## License

AuraPHP is open source software licensed under the MIT License.
