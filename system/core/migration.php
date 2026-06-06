<?php

namespace AuraCore;

class Migration
{
    protected $db;

    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    public function getDb()
    {
        if ($this->db === null) {
            $this->db = new Database();
        }
        return $this->db;
    }

    public function table($table)
    {
        return $this->getDb()->table($table);
    }

    public function schema()
    {
        if (!Schema::getPdo()) {
            Schema::setPdo($this->getDb()->getPdo());
        }
        return new Schema();
    }

    public function up()
    {
        // Override in child classes
    }

    public function down()
    {
        // Override in child classes
    }

    public static function resolveClass($base, $filename = null)
    {
        $name = $base;
        if ($filename !== null) {
            $name = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $filename);
            $name = preg_replace('/\.php$/', '', $name);
        } else {
            $name = preg_replace('/\.php$/', '', $name);
        }
        $class = 'Migration_' . self::studly($name);
        return str_replace('-', '_', $class);
    }

    protected static function studly($name)
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }
}
