<?php

namespace AuraCore;

use PDO;

class QueryBuilder
{
    protected $pdo;
    protected $database;
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
    protected $distinct = false;
    protected $modelClass;
    protected $primaryKey = 'id';
    protected $eagerLoads = [];
    protected $driver;

    public function __construct(PDO $pdo, $table = null)
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function setDatabase($database)
    {
        $this->database = $database;
        return $this;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function table($table)
    {
        $this->table = $table;
        return $this;
    }

    public function select($columns)
    {
        $this->selects = is_array($columns) ? array_values($columns) : func_get_args();
        return $this;
    }

    public function addSelect($columns)
    {
        $extra = is_array($columns) ? array_values($columns) : func_get_args();
        $this->selects = array_merge($this->selects, $extra);
        return $this;
    }

    public function distinct()
    {
        $this->distinct = true;
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if (is_array($value)) {
            return $this->whereIn($column, $value, 'AND', $operator);
        }

        $this->wheres[] = $this->makeWhere('basic', $column, $operator, $value, 'AND');
        return $this;
    }

    public function orWhere($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        if (is_array($value)) {
            return $this->whereIn($column, $value, 'OR', $operator);
        }

        $this->wheres[] = $this->makeWhere('basic', $column, $operator, $value, 'OR');
        return $this;
    }

    public function whereIn($column, array $values, $boolean = 'AND', $operator = '=')
    {
        if (empty($values)) {
            $this->wheres[] = [
                'type' => 'raw',
                'sql' => '1 = 0',
                'boolean' => $this->resolveBoolean($boolean),
                'bindings' => [],
            ];
            return $this;
        }

        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => array_values($values),
            'operator' => $operator === '=' ? 'IN' : 'NOT IN',
            'boolean' => $this->resolveBoolean($boolean),
        ];

        return $this;
    }

    public function whereNotIn($column, array $values)
    {
        return $this->whereIn($column, $values, 'AND', '!=');
    }

    public function orWhereIn($column, array $values)
    {
        return $this->whereIn($column, $values, 'OR');
    }

    public function whereNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $this->resolveBoolean($boolean),
        ];
        return $this;
    }

    public function whereNotNull($column, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'not_null',
            'column' => $column,
            'boolean' => $this->resolveBoolean($boolean),
        ];
        return $this;
    }

    public function whereBetween($column, array $values, $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'values' => array_values($values),
            'boolean' => $this->resolveBoolean($boolean),
            'not' => false,
        ];
        return $this;
    }

    public function whereRaw($sql, array $bindings = [], $boolean = 'AND')
    {
        $this->wheres[] = [
            'type' => 'raw',
            'sql' => $sql,
            'bindings' => $bindings,
            'boolean' => $this->resolveBoolean($boolean),
        ];
        return $this;
    }

    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        if (func_num_args() === 3) {
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
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orderBys[] = compact('column', 'direction');
        return $this;
    }

    public function orderByRaw($sql)
    {
        $this->orderBys[] = ['raw' => $sql];
        return $this;
    }

    public function groupBy(...$columns)
    {
        $this->groupBys = array_merge($this->groupBys, $columns);
        return $this;
    }

    public function having($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->havings[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];
        return $this;
    }

    public function limit($limit)
    {
        $this->limitValue = (int) $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offsetValue = (int) $offset;
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

    public function getModel()
    {
        return $this->modelClass;
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

    public function value($column)
    {
        $row = $this->select($column)->first();
        return $row ? ($row[$column] ?? null) : null;
    }

    public function pluck($column, $key = null)
    {
        $selects = $key ? [$column, $key] : [$column];
        $results = $this->select($selects)->get();

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

    public function first()
    {
        $clone = $this->cloneForAggregate();
        $clone->limit(1)->offset(0);
        $results = $clone->runSelect();

        return !empty($results) ? $results[0] : null;
    }

    public function get()
    {
        return $this->runSelect();
    }

    public function all()
    {
        return $this->get();
    }

    public function count($column = '*')
    {
        return (int) $this->aggregate('COUNT', $column);
    }

    public function sum($column)
    {
        $result = $this->aggregate('SUM', $column);
        return $result === null ? 0 : (float) $result;
    }

    public function avg($column)
    {
        $result = $this->aggregate('AVG', $column);
        return $result === null ? 0 : (float) $result;
    }

    public function min($column)
    {
        $result = $this->aggregate('MIN', $column);
        return $result === null ? 0 : (float) $result;
    }

    public function max($column)
    {
        $result = $this->aggregate('MAX', $column);
        return $result === null ? 0 : (float) $result;
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

        $columns = implode(', ', array_map([$this, 'quoteColumn'], array_keys($data)));
        $placeholders = rtrim(str_repeat('?, ', count($data)), ', ');

        $sql = "INSERT INTO {$this->wrapTable($this->table)} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->flattenValues($data));

        return (int) $this->pdo->lastInsertId();
    }

    public function insertBatch(array $data)
    {
        if (empty($data)) {
            return false;
        }

        $first = $data[0];
        $columns = implode(', ', array_map([$this, 'quoteColumn'], array_keys($first)));
        $rowPlaceholders = '(' . rtrim(str_repeat('?, ', count($first)), ', ') . ')';
        $allPlaceholders = implode(', ', array_fill(0, count($data), $rowPlaceholders));

        $sql = "INSERT INTO {$this->wrapTable($this->table)} ({$columns}) VALUES {$allPlaceholders}";

        $values = [];
        foreach ($data as $row) {
            foreach ($row as $v) {
                $values[] = $v;
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        return $stmt->rowCount();
    }

    public function update(array $data)
    {
        if (empty($data)) {
            return 0;
        }

        $sets = [];
        $values = [];
        foreach ($data as $key => $value) {
            $sets[] = "{$this->quoteColumn($key)} = ?";
            $values[] = $value;
        }
        $sets = implode(', ', $sets);

        $sql = "UPDATE {$this->wrapTable($this->table)} SET {$sets}";
        $whereSql = $this->compileWheres();
        if ($whereSql !== '') {
            $sql .= $whereSql;
        } else {
            throw new \RuntimeException('Update query must have a where clause to prevent accidental mass updates.');
        }
        $values = array_merge($values, $this->bindings);

        $stmt = $this->execute($sql, $values);
        return $stmt->rowCount();
    }

    public function delete()
    {
        $sql = "DELETE FROM {$this->wrapTable($this->table)}";
        $whereSql = $this->compileWheres();
        if ($whereSql === '') {
            throw new \RuntimeException('Delete query must have a where clause to prevent accidental mass deletion.');
        }
        $sql .= $whereSql;

        $stmt = $this->execute($sql, $this->bindings);
        return $stmt->rowCount();
    }

    public function truncate()
    {
        $sql = $this->driver === 'sqlite'
            ? "DELETE FROM {$this->wrapTable($this->table)}"
            : "TRUNCATE TABLE {$this->wrapTable($this->table)}";

        if ($this->driver === 'sqlite') {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name = " . $this->pdo->quote($this->table));
            return $stmt->rowCount();
        }

        return $this->pdo->exec($sql);
    }

    public function paginate($perPage = 15, $page = null, $baseUrl = null)
    {
        $page = $page !== null ? (int) $page : (int) ($_GET['page'] ?? 1);
        $page = max(1, $page);

        $total = $this->countForPagination();
        $perPage = max(1, (int) $perPage);
        $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;
        if ($page > $lastPage) {
            $page = $lastPage;
        }

        $items = $this->cloneForAggregate()
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->get();

        return new Pagination($items, (int) $total, $perPage, $page, $baseUrl);
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
        $qb->driver = $this->driver;
        $qb->database = $this->database;
        return $qb;
    }

    public function raw($value)
    {
        if ($value instanceof RawExpression) {
            return $value;
        }
        return new RawExpression((string) $value);
    }

    protected function compileSelect()
    {
        $distinct = $this->distinct ? 'DISTINCT ' : '';
        $columns = empty($this->selects) || (count($this->selects) === 1 && $this->selects[0] === '*')
            ? '*'
            : implode(', ', $this->selects);

        $sql = "SELECT {$distinct}{$columns} FROM {$this->wrapTable($this->table)}";

        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres();
        $sql .= $this->compileGroupBys();
        $sql .= $this->compileHavings();
        $sql .= $this->compileOrderBys();
        $sql .= $this->compileLimit();
        $sql .= $this->compileOffset();

        return $sql;
    }

    protected function compileCountSelect()
    {
        $sql = "SELECT COUNT(*) AS aggregate FROM {$this->wrapTable($this->table)}";
        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres();
        return $sql;
    }

    protected function compileJoins()
    {
        if (empty($this->joins)) {
            return '';
        }
        $sql = '';
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$this->wrapTable($join['table'])} ON {$join['first']} {$join['operator']} {$join['second']}";
        }
        return $sql;
    }

    protected function compileWheres()
    {
        if (empty($this->wheres)) {
            return '';
        }

        $this->bindings = [];

        $sql = '';
        foreach ($this->wheres as $i => $where) {
            $boolean = $i === 0 ? 'WHERE' : ($where['boolean'] ?? 'AND');

            switch ($where['type']) {
                case 'basic':
                    $sql .= " {$boolean} {$this->quoteColumn($where['column'])} {$where['operator']} ?";
                    $this->bindings[] = $where['value'];
                    break;

                case 'in':
                    $placeholders = rtrim(str_repeat('?, ', count($where['values'])), ', ');
                    $sql .= " {$boolean} {$this->quoteColumn($where['column'])} {$where['operator']} ({$placeholders})";
                    foreach ($where['values'] as $v) {
                        $this->bindings[] = $v;
                    }
                    break;

                case 'null':
                    $sql .= " {$boolean} {$this->quoteColumn($where['column'])} IS NULL";
                    break;

                case 'not_null':
                    $sql .= " {$boolean} {$this->quoteColumn($where['column'])} IS NOT NULL";
                    break;

                case 'between':
                    $sql .= " {$boolean} {$this->quoteColumn($where['column'])} BETWEEN ? AND ?";
                    $this->bindings[] = $where['values'][0] ?? null;
                    $this->bindings[] = $where['values'][1] ?? null;
                    break;

                case 'raw':
                    $sql .= " {$boolean} ({$where['sql']})";
                    foreach ($where['bindings'] as $b) {
                        $this->bindings[] = $b;
                    }
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
            if (($having['type'] ?? 'basic') === 'raw') {
                $clauses[] = $having['sql'];
            } else {
                $clauses[] = "{$having['column']} {$having['operator']} ?";
                $this->bindings[] = $having['value'];
            }
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
            if (isset($order['raw'])) {
                $clauses[] = $order['raw'];
            } else {
                $clauses[] = "{$this->quoteColumn($order['column'])} {$order['direction']}";
            }
        }

        return ' ORDER BY ' . implode(', ', $clauses);
    }

    protected function compileLimit()
    {
        if ($this->limitValue === null) {
            return '';
        }

        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
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

        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            $start = (int) $this->offsetValue + 1;
            $end = $start + (int) $this->limitValue - 1;
            $this->limitValue = null;
            return " OFFSET {$start} ROWS FETCH NEXT {$end} ROWS ONLY";
        }

        $this->bindings[] = $this->offsetValue;
        return ' OFFSET ?';
    }

    protected function aggregate($function, $column = '*')
    {
        $column = $column === '*' ? '*' : $this->quoteColumn($column);
        $sql = "SELECT {$function}({$column}) AS aggregate FROM {$this->wrapTable($this->table)}";
        $sql .= $this->compileJoins();
        $sql .= $this->compileWheres();

        $stmt = $this->execute($sql, $this->bindings);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $value = $result['aggregate'] ?? null;

        $this->bindings = [];
        return $value;
    }

    protected function countForPagination()
    {
        $clone = $this->cloneForAggregate();
        $clone->selects = [];
        $clone->orderBys = [];
        $clone->groupBys = [];
        $clone->havings = [];
        $clone->limitValue = null;
        $clone->offsetValue = null;
        $clone->eagerLoads = [];

        $sql = "SELECT COUNT(*) AS aggregate FROM {$this->wrapTable($clone->table)}";
        $sql .= $clone->compileJoins();
        $sql .= $clone->compileWheres();
        $sql .= $clone->compileGroupBys();
        $sql .= $clone->compileHavings();

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($clone->bindings);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clone->groupBys || $clone->havings) {
            return $stmt->rowCount();
        }
        return (int) ($row['aggregate'] ?? 0);
    }

    protected function runSelect()
    {
        $sql = $this->compileSelect();
        $stmt = $this->execute($sql, $this->bindings);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($this->modelClass) {
            $models = $this->hydrate($results);
            $this->loadEagerRelations($models);
            return $models;
        }

        return $results;
    }

    protected function cloneForAggregate()
    {
        $clone = clone $this;
        $clone->selects = $this->selects;
        $clone->eagerLoads = [];
        return $clone;
    }

    protected function makeWhere($type, $column, $operator, $value, $boolean)
    {
        return [
            'type' => $type,
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $this->resolveBoolean($boolean),
        ];
    }

    protected function resolveBoolean($boolean)
    {
        $b = strtoupper((string) $boolean);
        return $b === 'OR' ? 'OR' : 'AND';
    }

    protected function wrapTable($table)
    {
        if ($table === null) {
            throw new \RuntimeException('No table specified for query.');
        }
        if ($table instanceof RawExpression) {
            return (string) $table;
        }
        if (strpos($table, '.') !== false) {
            $parts = explode('.', $table);
            return implode('.', array_map([$this, 'quoteTable'], $parts));
        }
        if (strpos($table, ' ') !== false) {
            $parts = preg_split('/\s+/', $table, 2);
            return $this->quoteTable($parts[0]) . ' ' . $parts[1];
        }
        return $this->quoteTable($table);
    }

    protected function quoteTable($table)
    {
        if ($this->driver === 'mysql' || $this->driver === 'sqlite' || $this->driver === 'pgsql') {
            return '"' . str_replace('"', '""', $table) . '"';
        }
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $table) . ']';
        }
        return $table;
    }

    protected function quoteColumn($column)
    {
        if ($column instanceof RawExpression) {
            return (string) $column;
        }
        if (strpos($column, '(') !== false && strpos($column, ')') !== false) {
            return $column;
        }
        if (strpos($column, '.') !== false) {
            $parts = explode('.', $column);
            $last = array_pop($parts);
            $prefix = implode('.', $parts);
            return $prefix . '.' . $this->quoteSingleColumn($last);
        }
        if (strpos($column, ' ') !== false) {
            $parts = preg_split('/\s+/', $column, 2);
            return $this->quoteSingleColumn($parts[0]) . ' ' . $parts[1];
        }
        if (in_array(strtoupper($column), ['*', 'COUNT(*)', 'COUNT(*) AS AGGREGATE'], true)) {
            return $column;
        }
        return $this->quoteSingleColumn($column);
    }

    protected function quoteSingleColumn($column)
    {
        $column = trim($column);
        if ($column === '*') {
            return '*';
        }
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $column) . ']';
        }
        return '"' . str_replace('"', '""', $column) . '"';
    }

    protected function flattenValues(array $data)
    {
        $values = [];
        foreach ($data as $v) {
            $values[] = $v;
        }
        return $values;
    }

    protected function execute($sql, $bindings = null)
    {
        if ($bindings === null) {
            $bindings = $this->bindings;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        if ($this->database !== null) {
            $this->database->recordAffectedRows($stmt->rowCount());
        }

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
                $localKeys = array_values(array_unique(array_filter($localKeys, function ($v) {
                    return $v !== null;
                })));

                if (empty($localKeys)) break;

                $results = $relatedInstance->newQuery()
                    ->setModel($relatedClass)
                    ->whereIn($meta['foreignKey'], $localKeys)
                    ->get();

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
                $foreignKeys = array_values(array_unique(array_filter($foreignKeys, function ($v) {
                    return $v !== null;
                })));

                if (empty($foreignKeys)) break;

                $results = $relatedInstance->newQuery()
                    ->setModel($relatedClass)
                    ->whereIn($meta['localKey'], $foreignKeys)
                    ->get();

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
