<?php

namespace SiteControllers;
use AuraCore\Controller;

class Docs extends Controller {
    public function index() {
        $data = [
            'title' => 'AuraPHP Framework Documentation'
        ];
        $this->loadView('docs', $data);
    }
}
