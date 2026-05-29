<?php
/**
 * Controller Class
 * Base controller for all application controllers
 */

namespace AuraCore;
use AuraCore\Base;

class Controller extends Base {
    // Controller-specific methods can be added here
    
    /**
     * Example usage:
     * 
     * class Welcome extends Controller {
     *     public function index() {
     *         $this->loadView('welcome', [
     *             'title' => 'Welcome to AuraPHP',
     *             'message' => 'Powered by TailwindCSS'
     *         ]);
     *     }
     * }
     * 
     * TailwindCSS is globally available - just use <?php tailwind_css(); ?>
     * in your view files to include the compiled CSS.
     */
}
