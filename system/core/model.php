<?php

namespace AuraCore;

class Model extends Base
{
    protected $attributes = [];
    protected $original = [];
    protected $hidden = [];
    protected $visible = [];
    protected $casts = [];
    protected $fillable = [];
    protected $guarded = ['id'];
    protected $table;
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;
    public $exists = false;
    protected static $dbCache = [];

    protected $relations = [];
    protected $with = [];

    protected static $globalScopes = [];
    protected static $relationMeta = [];
    protected static $booted = [];

    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();
        $this->fill($attributes);
    }

    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    public function forceFill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        if (array_key_exists($key, $this->attributes)) {
            $value = $this->attributes[$key];
            return $this->castAttribute($key, $value);
        }

        $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->$method();
        }

        return null;
    }

    public function getRawAttribute($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    public function __unset($key)
    {
        unset($this->attributes[$key], $this->relations[$key]);
    }

    public function toArray()
    {
        $attributes = $this->attributes;

        foreach ($this->hidden as $key) {
            unset($attributes[$key]);
        }

        if (!empty($this->visible)) {
            $attributes = array_intersect_key($attributes, array_flip($this->visible));
        }

        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->castAttribute($key, $value);
        }

        foreach ($this->relations as $key => $value) {
            if (in_array($key, $this->hidden, true)) {
                continue;
            }
            if (!empty($this->visible) && !in_array($key, $this->visible, true)) {
                continue;
            }
            if ($value instanceof Model) {
                $attributes[$key] = $value->toArray();
            } elseif (is_array($value)) {
                $attributes[$key] = array_map(function ($item) {
                    return $item instanceof Model ? $item->toArray() : $item;
                }, $value);
            } else {
                $attributes[$key] = $value;
            }
        }

        return $attributes;
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public function setRawAttributes(array $attributes, $sync = false)
    {
        $this->attributes = $attributes;
        if ($sync) {
            $this->original = $attributes;
        }
        return $this;
    }

    public function getOriginal($key = null)
    {
        if ($key === null) {
            return $this->original;
        }
        return $this->original[$key] ?? null;
    }

    public function isDirty($key = null)
    {
        if ($key === null) {
            return $this->getDirty() !== [];
        }
        return ($this->attributes[$key] ?? null) !== ($this->original[$key] ?? null);
    }

    public function isClean($key = null)
    {
        return !$this->isDirty($key);
    }

    public function sync()
    {
        $this->original = $this->attributes;
        return $this;
    }

    public function save()
    {
        if ($this->timestamps) {
            $now = $this->freshTimestamp();
            if (!$this->exists) {
                if (!array_key_exists('created_at', $this->attributes)) {
                    $this->attributes['created_at'] = $now;
                }
                $this->attributes['updated_at'] = $now;
            } else {
                $this->attributes['updated_at'] = $now;
            }
        }

        $query = $this->newQuery();

        if ($this->exists) {
            $dirty = $this->getDirty();
            if (empty($dirty)) {
                return true;
            }

            $result = $query->where($this->primaryKey, $this->getKey())->update($dirty);
            if ($result !== false) {
                $this->sync();
                return true;
            }
            return false;
        }

        $id = $query->insert($this->attributes);

        if ($id && $this->incrementing) {
            $this->attributes[$this->primaryKey] = $this->castKey($id);
        }

        $this->exists = true;
        $this->sync();

        return true;
    }

    public function delete()
    {
        if (!$this->exists) {
            return false;
        }

        $query = $this->newQuery();
        $result = $query->where($this->primaryKey, $this->getKey())->delete();

        $this->exists = false;
        return $result > 0;
    }

    public function destroy($ids)
    {
        $ids = is_array($ids) ? $ids : func_get_args();
        if (empty($ids)) {
            return 0;
        }
        $query = $this->newQuery();
        return $query->whereIn($this->primaryKey, $ids)->delete();
    }

    public function getKey()
    {
        $key = $this->attributes[$this->primaryKey] ?? null;
        return $this->castKey($key);
    }

    public function getKeyName()
    {
        return $this->primaryKey;
    }

    public function getTable()
    {
        if ($this->table) {
            return $this->table;
        }

        $class = static::class;
        $class = basename(str_replace('\\', '/', $class));
        $class = preg_replace('/_?Model$/', '', $class);

        return $this->table = $this->pluralize($class);
    }

    public function getForeignKey()
    {
        $class = basename(str_replace('\\', '/', static::class));
        $class = preg_replace('/_?Model$/', '', $class);
        return strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $class)) . '_id';
    }

    public function fresh()
    {
        if (!$this->exists) {
            return null;
        }
        return static::find($this->getKey());
    }

    public function refresh()
    {
        $fresh = $this->fresh();
        if ($fresh) {
            $this->setRawAttributes($fresh->getRawAttributes(), true);
            $this->exists = true;
            $this->relations = $fresh->relations;
        }
        return $this;
    }

    // --- Relations ---

    public function getRelation($key)
    {
        return $this->relations[$key] ?? null;
    }

    public function setRelation($key, $value)
    {
        $this->relations[$key] = $value;
        return $this;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function unsetRelation($key)
    {
        unset($this->relations[$key]);
        return $this;
    }

    public function load($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();
        $allKeys = [];

        foreach ($relations as $key) {
            if (is_string($key) && strpos($key, '.') !== false) {
                $parts = explode('.', $key);
                $allKeys[] = $parts[0];
                $allKeys = array_merge($allKeys, $parts);
            } else {
                $allKeys[] = $key;
            }
        }

        $allKeys = array_unique($allKeys);

        foreach ($allKeys as $relation) {
            if (!method_exists($this, $relation)) {
                continue;
            }

            $this->relations[$relation] = $this->$relation()->get();
        }

        return $this;
    }

    public function loadMissing($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();
        $missing = array_filter($relations, function ($r) {
            return !array_key_exists($r, $this->relations);
        });
        if (!empty($missing)) {
            $this->load($missing);
        }
        return $this;
    }

    public static function with(...$relations)
    {
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        $query->with($relations);
        return $query;
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        $this->setRelationMeta('hasMany', $related, $foreignKey, $localKey);

        return $instance->newQuery()
            ->setModel($related)
            ->where($foreignKey, $this->getRawAttribute($localKey));
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $instance->getForeignKey();
        $ownerKey = $ownerKey ?: $instance->getKeyName();

        $this->setRelationMeta('belongsTo', $related, $foreignKey, $ownerKey);

        return $instance->newQuery()
            ->setModel($related)
            ->where($ownerKey, $this->getRawAttribute($foreignKey));
    }

    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        $this->setRelationMeta('hasOne', $related, $foreignKey, $localKey);

        return $instance->newQuery()
            ->setModel($related)
            ->where($foreignKey, $this->getRawAttribute($localKey));
    }

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null)
    {
        $instance = new $related;
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        if (!$table) {
            $segments = [
                $this->getShortClassName(),
                $instance->getShortClassName(),
            ];
            sort($segments);
            $table = strtolower(implode('_', $segments));
        }

        $localKey = $this->getKey();

        $query = (new static)->newQuery()
            ->setModel($related)
            ->select($instance->getTable() . '.*')
            ->join($table, $table . '.' . $relatedPivotKey, '=', $instance->getTable() . '.' . $instance->getKeyName())
            ->where($table . '.' . $foreignPivotKey, $localKey);

        return $query;
    }

    // --- Global Scopes ---

    public static function addGlobalScope($scope)
    {
        $class = static::class;
        if ($scope instanceof \Closure) {
            static::$globalScopes[$class][] = $scope;
        }
    }

    public static function withoutGlobalScopes()
    {
        $instance = new static;
        return $instance->newQueryWithoutScopes()->setModel(static::class);
    }

    // --- Query Methods ---

    public static function find($id)
    {
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        $result = $query->find($id);
        if ($result && $result instanceof Model) {
            $result->loadDefaultRelations();
        }
        return $result;
    }

    public static function findMany(array $ids)
    {
        if (empty($ids)) {
            return [];
        }
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        return $query->whereIn($instance->getKeyName(), $ids)->get();
    }

    public static function findOrFail($id)
    {
        $result = static::find($id);
        if (!$result) {
            throw new \RuntimeException(static::class . " with ID {$id} not found.");
        }
        return $result;
    }

    public static function all()
    {
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        return $query->get();
    }

    public static function where($column, $operator = null, $value = null)
    {
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        return $query->where($column, $operator, $value);
    }

    public static function count()
    {
        $instance = new static;
        return $instance->newQuery()->count();
    }

    public static function create(array $attributes)
    {
        $instance = new static($attributes);
        $instance->save();
        return $instance;
    }

    public static function firstOrCreate(array $attributes, array $values = [])
    {
        $instance = new static;
        $query = $instance->newQuery();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        $existing = $query->first();

        if ($existing) {
            return $existing;
        }

        return static::create(array_merge($attributes, $values));
    }

    public static function firstOrNew(array $attributes, array $values = [])
    {
        $instance = new static;
        $query = $instance->newQuery();

        foreach ($attributes as $key => $value) {
            $query->where($key, $value);
        }

        $existing = $query->first();

        if ($existing) {
            return $existing;
        }

        return new static(array_merge($attributes, $values));
    }

    public static function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = static::firstOrNew($attributes);
        $instance->forceFill($values);
        $instance->save();
        return $instance;
    }

    public static function pluck($column, $key = null)
    {
        $instance = new static;
        return $instance->newQuery()->pluck($column, $key);
    }

    public static function each(callable $callback)
    {
        foreach (static::all() as $model) {
            $callback($model);
        }
    }

    public static function chunk($size, callable $callback)
    {
        $page = 1;
        do {
            $results = (new static)->newQuery()
                ->setModel(static::class)
                ->paginate($size, $page);

            $items = $results->items();
            if (count($items) === 0) {
                break;
            }

            $callback($items, $page);

            $page++;
        } while ($results->hasMorePages());
    }

    public static function truncate()
    {
        $instance = new static;
        $instance->newQuery()->truncate();
    }

    // --- Internal ---

    protected function bootIfNotBooted()
    {
        $class = static::class;

        if (isset(static::$booted[$class])) {
            return;
        }

        static::$booted[$class] = true;
        $this->boot();
    }

    protected function boot()
    {
        $class = static::class;

        if (function_exists('class_uses_recursive')) {
            $traits = class_uses_recursive($class);
        } else {
            $traits = $this->classUsesRecursive($class);
        }

        foreach ($traits as $trait) {
            $trait = basename(str_replace('\\', '/', $trait));
            $method = 'boot' . $trait;
            if (method_exists($class, $method)) {
                $class::$method();
            }
        }
    }

    protected function classUsesRecursive($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $results = [];
        foreach (array_reverse(class_parents($class) ?: []) + [$class => $class] as $current) {
            $results += $this->traitUsesRecursive($current);
        }
        return array_unique($results);
    }

    protected function traitUsesRecursive($class)
    {
        $traits = class_uses($class) ?: [];
        foreach ($traits as $trait) {
            $traits += $this->traitUsesRecursive($trait);
        }
        return $traits;
    }

    protected function newQuery()
    {
        $db = $this->loadDatabase();
        $query = $db->table($this->getTable())
            ->setModel(static::class)
            ->setPrimaryKey($this->primaryKey);

        $this->applyGlobalScopes($query);
        $this->applyDefaultEagerLoads($query);

        return $query;
    }

    protected function newQueryWithoutScopes()
    {
        $db = $this->loadDatabase();
        return $db->table($this->getTable())
            ->setModel(static::class)
            ->setPrimaryKey($this->primaryKey);
    }

    protected function applyGlobalScopes($query)
    {
        $class = static::class;
        if (empty(static::$globalScopes[$class])) {
            return;
        }

        foreach (static::$globalScopes[$class] as $scope) {
            $scope($query);
        }
    }

    protected function applyDefaultEagerLoads($query)
    {
        if (!empty($this->with)) {
            $query->with($this->with);
        }
    }

    protected function loadDefaultRelations()
    {
        if (!empty($this->with)) {
            $this->load($this->with);
        }
    }

    protected function setRelationMeta($type, $related, $foreignKey, $localKey)
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        if (isset($trace[2])) {
            $method = $trace[2]['function'] ?? 'unknown';
            $class = static::class;
            static::$relationMeta[$class][$method] = [
                'type' => $type,
                'related' => $related,
                'foreignKey' => $foreignKey,
                'localKey' => $localKey,
            ];
        }
    }

    public static function getRelationMeta($relation)
    {
        $class = static::class;
        return static::$relationMeta[$class][$relation] ?? null;
    }

    public function getDirty()
    {
        $dirty = [];
        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }
        return $dirty;
    }

    protected function isFillable($key)
    {
        if ($this->hasFillable()) {
            return in_array($key, $this->fillable, true);
        }
        return !in_array($key, $this->guarded, true);
    }

    protected function hasFillable()
    {
        return !empty($this->fillable);
    }

    protected function castAttribute($key, $value)
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        $cast = $this->casts[$key];

        switch ($cast) {
            case 'int':
            case 'integer':
                return $value === null ? null : (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return $value === null ? null : (float) $value;
            case 'string':
                return $value === null ? null : (string) $value;
            case 'bool':
            case 'boolean':
                if ($value === null) {
                    return null;
                }
                if (is_bool($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    $lower = strtolower($value);
                    if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
                        return true;
                    }
                    if (in_array($lower, ['false', '0', 'no', 'off', ''], true)) {
                        return false;
                    }
                }
                return (bool) $value;
            case 'array':
            case 'json':
                if ($value === null) {
                    return null;
                }
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    return json_decode($value, true);
                }
                return (array) $value;
            case 'object':
                if ($value === null) {
                    return null;
                }
                if (is_object($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    return json_decode($value);
                }
                return (object) $value;
            case 'collection':
                if ($value === null) {
                    return [];
                }
                if (is_array($value)) {
                    return $value;
                }
                if (is_string($value)) {
                    return json_decode($value, true) ?? [];
                }
                return (array) $value;
            case 'date':
                return $value ? new \DateTime($value) : null;
            case 'datetime':
                return $value ? new \DateTime($value) : null;
            case 'timestamp':
                return $value ? (int) strtotime($value) : null;
            default:
                return $value;
        }
    }

    protected function castKey($value)
    {
        if ($value === null) {
            return null;
        }
        if ($this->keyType === 'int') {
            return (int) $value;
        }
        if ($this->keyType === 'string') {
            return (string) $value;
        }
        return $value;
    }

    protected function freshTimestamp()
    {
        return date('Y-m-d H:i:s');
    }

    protected function getShortClassName()
    {
        $class = basename(str_replace('\\', '/', static::class));
        return preg_replace('/_?Model$/', '', $class);
    }

    protected function pluralize($word)
    {
        $word = strtolower(preg_replace('/(?<!^)([A-Z])/', '_$1', $word));

        $irregular = [
            'person' => 'people',
            'man' => 'men',
            'woman' => 'women',
            'child' => 'children',
            'foot' => 'feet',
            'tooth' => 'teeth',
            'mouse' => 'mice',
            'goose' => 'geese',
        ];
        if (isset($irregular[$word])) {
            return $irregular[$word];
        }

        $uncountable = ['info', 'equipment', 'fish', 'sheep', 'series', 'species', 'money', 'data'];
        if (in_array($word, $uncountable, true)) {
            return $word;
        }

        if (preg_match('/(s|x|z|ch|sh)$/', $word)) {
            return $word . 'es';
        }
        if (preg_match('/([^aeiou])y$/', $word)) {
            return substr($word, 0, -1) . 'ies';
        }
        if (preg_match('/(f|fe)$/', $word)) {
            if (substr($word, -2) === 'fe') {
                return substr($word, 0, -2) . 'ves';
            }
            return substr($word, 0, -1) . 'ves';
        }
        if (preg_match('/(o)$/', $word)) {
            return $word . 'es';
        }
        return $word . 's';
    }
}
