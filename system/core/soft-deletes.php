<?php

namespace AuraCore;

trait SoftDeletes
{
    protected static $trashedColumn = 'deleted_at';

    public static function bootSoftDeletes()
    {
        static::addGlobalScope(function ($query) {
            $instance = new static;
            $table = $instance->getTable();
            $column = static::$trashedColumn;
            $query->whereNull("{$table}.{$column}");
        });
    }

    public function delete()
    {
        if (!$this->exists) {
            return false;
        }

        if ($this->fireModelEvent('trashing') === false) {
            return false;
        }

        $this->attributes[static::$trashedColumn] = $this->freshTimestamp();
        $result = $this->save();

        if ($result) {
            $this->fireModelEvent('trashed');
        }

        return $result;
    }

    public function forceDelete()
    {
        if (!$this->exists) {
            return false;
        }

        if ($this->fireModelEvent('forceDeleting') === false) {
            return false;
        }

        $query = $this->newQueryWithoutScopes();
        $result = $query->where($this->primaryKey, $this->getKey())->delete();

        $this->exists = false;

        if ($result) {
            $this->fireModelEvent('forceDeleted');
        }

        return $result > 0;
    }

    public function restore()
    {
        if (!$array_key_exists = array_key_exists(static::$trashedColumn, $this->attributes)) {
            return false;
        }

        if ($this->fireModelEvent('restoring') === false) {
            return false;
        }

        $this->attributes[static::$trashedColumn] = null;
        $result = $this->save();

        if ($result) {
            $this->fireModelEvent('restored');
        }

        return $result;
    }

    public function trashed()
    {
        $value = $this->attributes[static::$trashedColumn] ?? null;
        return $value !== null && $value !== '0000-00-00 00:00:00' && $value !== '';
    }

    public static function withTrashed()
    {
        $instance = new static;
        $query = $instance->newQueryWithoutScopes();
        $query->setModel(static::class);
        return $query;
    }

    public static function onlyTrashed()
    {
        $instance = new static;
        $table = $instance->getTable();
        $column = static::$trashedColumn;

        $query = $instance->newQueryWithoutScopes();
        $query->setModel(static::class);
        $query->whereNotNull("{$table}.{$column}");

        return $query;
    }

    protected function fireModelEvent($event)
    {
        $method = 'fire' . ucfirst($event) . 'Event';
        if (method_exists($this, $method)) {
            return $this->$method();
        }
        return true;
    }
}
