<?php
/**
 * Documentation Controller
 * Displays OwnStrap documentation
 */

namespace SiteControllers;
use AuraCore\Controller;

class Documentation extends Controller {
    public function index() {
        $data = [
            'title' => 'OwnStrap Documentation - AuraPHP'
        ];
        $this->loadView('documentation', $data);
    }
}
?>