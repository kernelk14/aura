<?php

namespace AuraCore;

class Model extends Base
{
    protected $attributes = [];
    protected $original = [];
    protected $hidden = [];
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

    public function toArray()
    {
        $attributes = $this->attributes;

        foreach ($this->hidden as $key) {
            unset($attributes[$key]);
        }

        foreach ($attributes as $key => $value) {
            $attributes[$key] = $this->castAttribute($key, $value);
        }

        foreach ($this->relations as $key => $value) {
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

    public function setRawAttributes(array $attributes)
    {
        $this->attributes = $attributes;
        $this->original = $attributes;
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
            return $this->attributes !== $this->original;
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
            $now = date('Y-m-d H:i:s');
            if (!$this->exists) {
                if (!isset($this->attributes['created_at'])) {
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
            $this->attributes[$this->primaryKey] = (int) $id;
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
        $query = $this->newQuery();
        return $query->whereIn($this->primaryKey, $ids)->delete();
    }

    public function getKey()
    {
        return $this->attributes[$this->primaryKey] ?? null;
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

        return $this->table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $class)) . 's';
    }

    public function getForeignKey()
    {
        $class = basename(str_replace('\\', '/', static::class));
        $class = preg_replace('/_?Model$/', '', $class);
        return strtolower($class) . '_id';
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
            $this->attributes = $fresh->attributes;
            $this->original = $fresh->original;
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

    public function load($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $relation) {
            if (!method_exists($this, $relation)) {
                continue;
            }

            $results = $this->$relation()->get();
            $this->setRelation($relation, $results);
        }

        return $this;
    }

    public static function with(...$relations)
    {
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        $query->eagerLoads($relations);
        return $query;
    }

    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        $this->setRelationMeta('hasMany', $related, $foreignKey, $localKey);

        return $instance->where($foreignKey, $this->getAttribute($localKey));
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $instance->getForeignKey();
        $ownerKey = $ownerKey ?: $instance->getKeyName();

        $this->setRelationMeta('belongsTo', $related, $foreignKey, $ownerKey);

        return $instance->where($ownerKey, $this->getAttribute($foreignKey));
    }

    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $instance = new $related;
        $foreignKey = $foreignKey ?: $this->getForeignKey();
        $localKey = $localKey ?: $this->primaryKey;

        $this->setRelationMeta('hasOne', $related, $foreignKey, $localKey);

        return $instance->where($foreignKey, $this->getAttribute($localKey));
    }

    public function belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null)
    {
        $instance = new $related;
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        if (!$table) {
            $segments = [
                basename(str_replace('\\', '/', static::class)),
                basename(str_replace('\\', '/', $related)),
            ];
            sort($segments);
            $table = strtolower(implode('_', $segments));
        }

        $localKey = $this->getKey();

        $query = (new static)->newQuery()
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
        $instance = new static;
        $query = $instance->newQuery();
        $query->setModel(static::class);
        return $query->whereIn($instance->getKeyName(), $ids)->get();
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
        $results = $instance->newQuery()->select([$column] + ($key ? [$key] : []))->get();

        if ($key) {
            $map = [];
            foreach ($results as $row) {
                $map[$row[$key]] = $row[$column];
            }
            return $map;
        }

        return array_map(function ($row) use ($column) {
            return $row[$column];
        }, $results);
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

            if (count($results->items()) === 0) {
                break;
            }

            $callback($results->items(), $page);

            $page++;
        } while ($results->hasMorePages());
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

        foreach (class_uses_recursive($class) as $trait) {
            $trait = basename(str_replace('\\', '/', $trait));
            $method = 'boot' . $trait;
            if (method_exists($class, $method)) {
                $class::$method();
            }
        }
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
            $query->eagerLoads($this->with);
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

    protected function getDirty()
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
            return in_array($key, $this->fillable);
        }
        return !in_array($key, $this->guarded);
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
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
            case 'json':
                return json_decode($value, true) ?? [];
            case 'object':
                return json_decode($value) ?? new \stdClass;
            case 'date':
                return $value ? new \DateTime($value) : null;
            default:
                return $value;
        }
    }
}
