<?php
/**
 * URL Helper
 * Provides functions for working with URLs
 */

/**
 * Get the base URL of the application
 * @param string $path Optional path to append
 * @return string Base URL or full path URL
 */
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $script = substr($script, 0, strrpos($script, '/'));
    $base = $protocol . "://{$host}{$script}/";
    return $base . ltrim($path, '/');
}

/**
 * Generate a URL
 * @param string $uri URI to append to base URL
 * @return string Full URL
 */
function site_url($uri = '') {
    return base_url() . ltrim($uri, '/');
}

/**
 * Redirect to a URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

/**
 * Output Ownstrap CSS and JS links
 * Include this in the <head> of your HTML
 * @return void
 */
function ownstrap_css() {
    echo '<link href="' . base_url('public/css/ownstrap.css') . '" rel="stylesheet">';
    echo '<link href="' . base_url('public/css/ownstrap-colors.css') . '" rel="stylesheet">';
}

/**
 * Output Ownstrap JavaScript
 * Include this at the end of your HTML or in <head>
 * @return void
 */
function ownstrap_js() {
    echo '<script src="' . base_url('public/js/ownstrap.js') . '"></script>';
}

/**
 * Load a template partial from site/templates/
 * @param string $name Template name (e.g. 'sidebar-framework' loads site/templates/sidebar-framework.php)
 */
function template($name) {
    include __DIR__ . '/../../site/templates/' . $name . '.php';
}