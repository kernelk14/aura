<?php

namespace AuraCore;

class Seeder
{
    protected $db;

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    public function call($seederClass)
    {
        $seeder = new $seederClass;
        $seeder->setDb($this->db);
        $seeder->run();
    }

    public function run()
    {
        // Override in child classes
    }
}
