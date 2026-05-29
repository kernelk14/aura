<?php
/**
 * Configuration File
 * 
 * AuraPHP Configuration
 */

return [
    'base_url' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 
        "https://{$_SERVER['HTTP_HOST']}" : 
        "http://{$_SERVER['HTTP_HOST']}",
    // Add more configuration items as needed
];