<?php
/**
 * Welcome Controller
 */

namespace SiteControllers;
use AuraCore\Controller;

class Welcome extends Controller {
    public function index() {
        $data = [
            'title' => 'Welcome to AuraPHP',
            'message' => 'This is a minimal PHP framework.'
        ];
        $this->loadView('welcome', $data);
    }
    
    public function test($param = '') {
        echo "Test method called with param: " . htmlspecialchars($param);
    }
}