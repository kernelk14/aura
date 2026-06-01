<?php

namespace AuraCore;

use PDO;

class QueryBuilder
{
    protected $pdo;
    protected $table;
    protected $selects = ['*'];
    protected $wheres = [];
    protected $joins = [];
    protected $orderBys = [];
    protected $groupBys = [];
    protected $havings = [];
    protected $limitValue;
    protected $offsetValue;
    protected $bindings = [];
    protected $modelClass;
    protected $primaryKey = 'id';
    protected $eagerLoads = [];

    public function __construct(PDO $pdo, $table = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($columns)
    {
        $this->selects = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => !empty($this->wheres) ? 'AND' : 'WHERE',
        ];

        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'OR',
        ];

        return $this;
    }

    public function whereIn($column, array $values)
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => !empty($this->wheres) ? 'AND' : 'WHERE',
        ];

        return $this;
    }

    public function whereNull($column)
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => !empty($this->wheres) ? 'AND' : 'WHERE',
        ];

        return $this;
    }

    public function whereNotNull($column)
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => !empty($this->wheres) ? 'AND' : 'WHERE',
        ];

        return $this;
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        if ($second === null) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }

    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBys[] = compact('column', 'direction');
        return $this;
    }

    public function groupBy(...$columns)
    {
        $this->groupBys = array_merge($this->groupBys, $columns);
        return $this;
    }

    public function having($column, $operator = null, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = compact('column', 'operator', 'value');
        return $this;
    }

    public function limit($limit)
    {
        $this->limitValue = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offsetValue = $offset;
        return $this;
    }

    public function take($limit)
    {
        return $this->limit($limit);
    }

    public function skip($offset)
    {
        return $this->offset($offset);
    }

    public function setModel($modelClass)
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    public function setPrimaryKey($key)
    {
        $this->primaryKey = $key;
        return $this;
    }

    public function with(...$relations)
    {
        $this->eagerLoads = array_merge($this->eagerLoads, $relations);
        return $this;
    }

    public function eagerLoads(array $relations)
    {
        $this->eagerLoads = $relations;
        return $this;
    }

    public function find($id)
    {
        return $this->where($this->primaryKey, $id)->first();
    }

    public function first()
    {
        $this->limit(1);
        $results = $this->get();
        return !empty($results) ? $results[0] : null;
    }

    public function get()
    {
        $sql = $this->compileSelect();
        $stmt = $this->execute($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->modelClass) {
            $models = $this->hydrate($results);
            $this->loadEagerRelations($models);
            return $models;
        }

        return $results;
    }

    public function all()
    {
        return $this->get();
    }

    public function count()
    {
        return $this->aggregate('COUNT');
    }

    public function sum($column)
    {
        return $this->aggregate('SUM', $column);
    }

    public function avg($column)
    {
        return $this->aggregate('AVG', $column);
    }

    public function min($column)
    {
        return $this->aggregate('MIN', $column);
    }

    public function max($column)
    {
        return $this->aggregate('MAX', $column);
    }

    public function exists()
    {
        return $this->count() > 0;
    }

    public function doesntExist()
    {
        return !$this->exists();
    }

    public function insert(array $data)
    {
        if (empty($data)) {
            return false;
        }

        if (isset($data[0]) && is_array($data[0])) {
            return $this->insertBatch($data);
        }

        $columns = '`' . implode('`, `', array_keys($data)) . '`';
        $placeholders = rtrim(str_repeat('?, ', count($data)), ', ');

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function insertBatch(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $columns = '`' . implode('`, `', array_keys($data[0])) . '`';
        $rowPlaceholders = '(' . rtrim(str_repeat('?, ', count($data[0])), ', ') . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($data), $rowPlaceholders));

        $sql = "INSERT INTO `{$this->table}` ({$columns}) VALUES {$allPlaceholders}";

        $values = [];
        foreach ($data as $row) {
            $values = array_merge($values, array_values($row));
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        return $stmt->rowCount();
    }

    public function update(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $sets = '';
        $values = [];
        foreach ($data as $key => $value) {
            $sets .= "`$key` = ?, ";
            $values[] = $value;
        }
        $sets = rtrim($sets, ', ');

        $sql = "UPDATE `{$this->table}` SET {$sets}";
        $sql .= $this->compileWheres();
        $values = array_merge($values, $this->getWhereBindings());

        $stmt = $this->execute($sql, $values);
        return $stmt->rowCount();
    }

    public function delete()
    {
        $sql = "DELETE FROM `{$this->table}`";
        $sql .= $this->compileWheres();
        $values = $this->getWhereBindings();

        $stmt = $this->execute($sql, $values);
        return $stmt->rowCount();
    }

    public function truncate()
    {
        $sql = "TRUNCATE TABLE `{$this->table}`";
        return $this->pdo->exec($sql);
    }

    public function paginate($perPage = 15, $page = null)
    {
        $page = $page ?: (isset($_GET['page']) ? (int) $_GET['page'] : 1);
        $total = $this->count();
        $lastPage = (int) ceil($total / $perPage);
        $page = max(1, min($page, $lastPage > 0 ? $lastPage : 1));

        $this->limit($perPage);
        $this->offset(($page - 1) * $perPage);

        $items = $this->get();

        return new Pagination($items, $total, $perPage, $page);
    }

    public function toSql()
    {
        return $this->compileSelect();
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    public function newQuery()
    {
        $qb = new self($this->pdo, $this->table);
        $qb->modelClass = $this->modelClass;
        $qb->primaryKey = $this->primaryKey;
        return $qb;
    }

    protected function compileSelect()
    {
        $columns = implode(', ', $this->selects);
        $sql = "SELECT {$columns} FROM `{$this->table}`";

        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres();
        $sql .= $this->compileGroupBys();
        $sql .= $this->compileHavings();
        $sql .= $this->compileOrderBys();
        $sql .= $this->compileLimit();
        $sql .= $this->compileOffset();

        return $sql;
    }

    protected function compileJoins()
    {
        $sql = '';
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN `{$join['table']}` ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return $sql;
    }

    protected function compileWheres()
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = '';
        $this->bindings = [];

        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? 'WHERE' : $where['boolean'];

            switch ($where['type']) {
                case 'basic':
                    $sql .= " {$boolean} `{$where['column']}` {$where['operator']} ?";
                    $this->bindings[] = $where['value'];
                    break;

                case 'in':
                    $placeholders = rtrim(str_repeat('?, ', count($where['values'])), ', ');
                    $sql .= " {$boolean} `{$where['column']}` IN ({$placeholders})";
                    $this->bindings = array_merge($this->bindings, $where['values']);
                    break;

                case 'null':
                    $sql .= " {$boolean} `{$where['column']}` IS NULL";
                    break;

                case 'not_null':
                    $sql .= " {$boolean} `{$where['column']}` IS NOT NULL";
                    break;
            }
        }

        return $sql;
    }

    protected function compileGroupBys()
    {
        if (empty($this->groupBys)) {
            return '';
        }
        return ' GROUP BY ' . implode(', ', $this->groupBys);
    }

    protected function compileHavings()
    {
        if (empty($this->havings)) {
            return '';
        }

        $clauses = [];
        foreach ($this->havings as $having) {
            $clauses[] = "{$having['column']} {$having['operator']} ?";
        }

        return ' HAVING ' . implode(' AND ', $clauses);
    }

    protected function compileOrderBys()
    {
        if (empty($this->orderBys)) {
            return '';
        }

        $clauses = [];
        foreach ($this->orderBys as $order) {
            $clauses[] = "{$order['column']} {$order['direction']}";
        }

        return ' ORDER BY ' . implode(', ', $clauses);
    }

    protected function compileLimit()
    {
        if ($this->limitValue === null) {
            return '';
        }

        $this->bindings[] = $this->limitValue;
        return ' LIMIT ?';
    }

    protected function compileOffset()
    {
        if ($this->offsetValue === null) {
            return '';
        }

        $this->bindings[] = $this->offsetValue;
        return ' OFFSET ?';
    }

    protected function getWhereBindings()
    {
        $bindings = [];
        foreach ($this->wheres as $where) {
            switch ($where['type']) {
                case 'basic':
                    $bindings[] = $where['value'];
                    break;
                case 'in':
                    $bindings = array_merge($bindings, $where['values']);
                    break;
            }
        }
        if ($this->limitValue !== null) {
            $bindings[] = $this->limitValue;
        }
        if ($this->offsetValue !== null) {
            $bindings[] = $this->offsetValue;
        }
        return $bindings;
    }

    protected function aggregate($function, $column = '*')
    {
        $this->selects = ["{$function}({$column}) as aggregate"];
        $sql = $this->compileSelect();
        $stmt = $this->execute($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (int) $result['aggregate'] : 0;
    }

    protected function execute($sql, $bindings = null)
    {
        if ($bindings === null) {
            $bindings = $this->bindings;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    protected function hydrate(array $results)
    {
        $models = [];
        $class = $this->modelClass;

        foreach ($results as $attributes) {
            $model = new $class;
            $model->setRawAttributes($attributes);
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    protected function loadEagerRelations($models)
    {
        if (empty($this->eagerLoads) || empty($models)) {
            return;
        }

        foreach ($this->eagerLoads as $relation) {
            $this->eagerLoadRelation($models, $relation);
        }
    }

    protected function eagerLoadRelation($models, $relation)
    {
        $class = $this->modelClass;
        $meta = $class::getRelationMeta($relation);

        if (!$meta) {
            foreach ($models as $model) {
                $model->load($relation);
            }
            return;
        }

        $relatedClass = $meta['related'];
        $relatedInstance = new $relatedClass;

        switch ($meta['type']) {
            case 'hasMany':
            case 'hasOne':
                $localKeys = array_map(function ($m) use ($meta) {
                    return $m->getAttribute($meta['localKey']);
                }, $models);
                $localKeys = array_unique(array_filter($localKeys));

                if (empty($localKeys)) break;

                $results = $relatedInstance->whereIn($meta['foreignKey'], $localKeys)->get();

                $grouped = [];
                foreach ($results as $related) {
                    $fk = $related->getAttribute($meta['foreignKey']);
                    $grouped[$fk][] = $related;
                }

                foreach ($models as $model) {
                    $key = $model->getAttribute($meta['localKey']);
                    if ($meta['type'] === 'hasOne') {
                        $model->setRelation($relation, $grouped[$key][0] ?? null);
                    } else {
                        $model->setRelation($relation, $grouped[$key] ?? []);
                    }
                }
                break;

            case 'belongsTo':
                $foreignKeys = array_map(function ($m) use ($meta) {
                    return $m->getAttribute($meta['foreignKey']);
                }, $models);
                $foreignKeys = array_unique(array_filter($foreignKeys));

                if (empty($foreignKeys)) break;

                $results = $relatedInstance->whereIn($meta['localKey'], $foreignKeys)->get();

                $keyed = [];
                foreach ($results as $related) {
                    $keyed[$related->getAttribute($meta['localKey'])] = $related;
                }

                foreach ($models as $model) {
                    $fk = $model->getAttribute($meta['foreignKey']);
                    $model->setRelation($relation, $keyed[$fk] ?? null);
                }
                break;
        }
    }

    public function __call($method, $args)
    {
        if ($this->modelClass) {
            $class = $this->modelClass;
            if (method_exists($class, $method)) {
                $model = new $class;
                return $model->$method(...$args);
            }
        }
        throw new \BadMethodCallException("Method {$method} does not exist.");
    }
}
