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

        $this->attributes[static::$trashedColumn] = date('Y-m-d H:i:s');
        $result = $this->save();

        return $result;
    }

    public function forceDelete()
    {
        if (!$this->exists) {
            return false;
        }

        $query = $this->newQuery();
        $result = $query->where($this->primaryKey, $this->getKey())->delete();

        $this->exists = false;
        return $result > 0;
    }

    public function restore()
    {
        $this->attributes[static::$trashedColumn] = null;
        return $this->save();
    }

    public function trashed()
    {
        return $this->attributes[static::$trashedColumn] !== null;
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
}
