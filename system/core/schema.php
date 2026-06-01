<?php

namespace AuraCore;

class Schema
{
    protected static $pdo;

    public static function setPdo($pdo)
    {
        self::$pdo = $pdo;
    }

    public static function getPdo()
    {
        if (!self::$pdo) {
            $db = new Database();
            self::$pdo = $db->getPdo();
        }
        return self::$pdo;
    }

    public static function create($table, $callback)
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toCreateSql();
        self::getPdo()->exec($sql);
    }

    public static function table($table, $callback)
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $sql = $blueprint->toAlterSql();
        if ($sql) {
            self::getPdo()->exec($sql);
        }
    }

    public static function drop($table)
    {
        self::getPdo()->exec("DROP TABLE IF EXISTS `{$table}`");
    }

    public static function dropIfExists($table)
    {
        self::getPdo()->exec("DROP TABLE IF EXISTS `{$table}`");
    }

    public static function hasTable($table)
    {
        $pdo = self::getPdo();
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='{$table}'");
        } else {
            $stmt = $pdo->query("SHOW TABLES LIKE '{$table}'");
        }

        return $stmt->fetch() !== false;
    }

    public static function hasColumn($table, $column)
    {
        $pdo = self::getPdo();
        $driver = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $stmt = $pdo->query("PRAGMA table_info('{$table}')");
        } else {
            $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}'");
        }

        $columns = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $colName = $col['Field'] ?? $col['name'] ?? null;
            if ($colName === $column) {
                return true;
            }
        }
        return false;
    }
}

class Blueprint
{
    protected $table;
    protected $columns = [];
    protected $commands = [];
    protected $driver = 'mysql';
    protected $engine = 'InnoDB';
    protected $charset = 'utf8';
    protected $collation = 'utf8_unicode_ci';

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function increments($column)
    {
        return $this->addColumn('increments', $column);
    }

    public function bigIncrements($column)
    {
        return $this->addColumn('bigIncrements', $column);
    }

    public function string($column, $length = 255)
    {
        return $this->addColumn('string', $column, compact('length'));
    }

    public function char($column, $length = 255)
    {
        return $this->addColumn('char', $column, compact('length'));
    }

    public function text($column)
    {
        return $this->addColumn('text', $column);
    }

    public function mediumText($column)
    {
        return $this->addColumn('mediumText', $column);
    }

    public function longText($column)
    {
        return $this->addColumn('longText', $column);
    }

    public function integer($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('integer', $column, compact('autoIncrement', 'unsigned'));
    }

    public function bigInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('bigInteger', $column, compact('autoIncrement', 'unsigned'));
    }

    public function tinyInteger($column, $unsigned = false)
    {
        return $this->addColumn('tinyInteger', $column, compact('unsigned'));
    }

    public function smallInteger($column, $unsigned = false)
    {
        return $this->addColumn('smallInteger', $column, compact('unsigned'));
    }

    public function boolean($column)
    {
        return $this->addColumn('boolean', $column);
    }

    public function decimal($column, $precision = 8, $scale = 2)
    {
        return $this->addColumn('decimal', $column, compact('precision', 'scale'));
    }

    public function float($column, $precision = 8, $scale = 2)
    {
        return $this->addColumn('float', $column, compact('precision', 'scale'));
    }

    public function double($column, $precision = 15, $scale = 8)
    {
        return $this->addColumn('double', $column, compact('precision', 'scale'));
    }

    public function date($column)
    {
        return $this->addColumn('date', $column);
    }

    public function dateTime($column)
    {
        return $this->addColumn('dateTime', $column);
    }

    public function time($column)
    {
        return $this->addColumn('time', $column);
    }

    public function timestamp($column)
    {
        return $this->addColumn('timestamp', $column, ['default' => null]);
    }

    public function timestamps()
    {
        $this->timestamp('created_at')->nullable();
        $this->timestamp('updated_at')->nullable();
    }

    public function softDeletes()
    {
        $this->timestamp('deleted_at')->nullable();
    }

    public function binary($column)
    {
        return $this->addColumn('binary', $column);
    }

    public function json($column)
    {
        return $this->addColumn('json', $column);
    }

    public function jsonb($column)
    {
        return $this->addColumn('jsonb', $column);
    }

    public function enum($column, array $allowed)
    {
        return $this->addColumn('enum', $column, compact('allowed'));
    }

    public function foreign($column)
    {
        $foreign = new ForeignKey($column);
        $this->commands['foreign'][] = $foreign;
        return $foreign;
    }

    public function engine($engine)
    {
        $this->engine = $engine;
        return $this;
    }

    public function charset($charset)
    {
        $this->charset = $charset;
        return $this;
    }

    public function collation($collation)
    {
        $this->collation = $collation;
        return $this;
    }

    public function indexes($columns, $name = null, $type = 'INDEX')
    {
        $this->commands['index'][] = compact('columns', 'name', 'type');
        return $this;
    }

    public function unique($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        return $this->indexes($columns, $name, 'UNIQUE');
    }

    public function primary($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        return $this->indexes($columns, $name, 'PRIMARY KEY');
    }

    public function dropColumn($column)
    {
        $this->commands['dropColumn'][] = $column;
        return $this;
    }

    public function renameColumn($from, $to)
    {
        $this->commands['renameColumn'][] = compact('from', 'to');
        return $this;
    }

    public function dropPrimary($name = null)
    {
        $this->commands['dropPrimary'][] = $name;
        return $this;
    }

    public function dropUnique($name)
    {
        $this->commands['dropUnique'][] = $name;
        return $this;
    }

    public function dropIndex($name)
    {
        $this->commands['dropIndex'][] = $name;
        return $this;
    }

    public function dropForeign($name)
    {
        $this->commands['dropForeign'][] = $name;
        return $this;
    }

    public function toCreateSql()
    {
        $columns = [];
        $indexes = [];
        $foreignKeys = [];

        foreach ($this->columns as $column) {
            $columns[] = $this->compileColumn($column);
            if ($column['type'] === 'increments') {
                $indexes[] = 'PRIMARY KEY (`' . $column['name'] . '`)';
            } elseif ($column['type'] === 'bigIncrements') {
                $indexes[] = 'PRIMARY KEY (`' . $column['name'] . '`)';
            }
        }

        foreach ($this->commands as $type => $items) {
            switch ($type) {
                case 'index':
                    foreach ($items as $idx) {
                        $colList = implode('`, `', (array) $idx['columns']);
                        $name = $idx['name'] ? "`{$idx['name']}` " : '';
                        if ($idx['type'] === 'PRIMARY KEY') {
                            $indexes[] = "PRIMARY KEY (`{$colList}`)";
                        } else {
                            $indexes[] = "{$idx['type']} {$name}(`{$colList}`)";
                        }
                    }
                    break;
                case 'foreign':
                    foreach ($items as $fk) {
                        $foreignKeys[] = (string) $fk;
                    }
                    break;
            }
        }

        $all = array_merge($columns, $indexes, $foreignKeys);
        $columnDefs = implode(",\n  ", $all);

        return "CREATE TABLE `{$this->table}` (\n  {$columnDefs}\n) ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation}";
    }

    public function toAlterSql()
    {
        $parts = [];

        foreach ($this->columns as $column) {
            $parts[] = "ADD " . $this->compileColumn($column);
        }

        foreach ($this->commands as $type => $items) {
            switch ($type) {
                case 'index':
                    foreach ($items as $idx) {
                        $colList = implode('`, `', (array) $idx['columns']);
                        $name = $idx['name'] ? "`{$idx['name']}` " : '';
                        if ($idx['type'] === 'PRIMARY KEY') {
                            $parts[] = "ADD PRIMARY KEY (`{$colList}`)";
                        } else {
                            $parts[] = "ADD {$idx['type']} {$name}(`{$colList}`)";
                        }
                    }
                    break;
                case 'foreign':
                    foreach ($items as $fk) {
                        $parts[] = "ADD " . (string) $fk;
                    }
                    break;
                case 'dropColumn':
                    foreach ($items as $col) {
                        $parts[] = "DROP COLUMN `{$col}`";
                    }
                    break;
                case 'renameColumn':
                    foreach ($items as $rc) {
                        $parts[] = "CHANGE `{$rc['from']}` `{$rc['to']}`";
                    }
                    break;
                case 'dropPrimary':
                    $parts[] = "DROP PRIMARY KEY";
                    break;
                case 'dropUnique':
                    foreach ($items as $name) {
                        $parts[] = "DROP INDEX `{$name}`";
                    }
                    break;
                case 'dropIndex':
                    foreach ($items as $name) {
                        $parts[] = "DROP INDEX `{$name}`";
                    }
                    break;
                case 'dropForeign':
                    foreach ($items as $name) {
                        $parts[] = "DROP FOREIGN KEY `{$name}`";
                    }
                    break;
            }
        }

        if (empty($parts)) {
            return '';
        }

        return "ALTER TABLE `{$this->table}`\n  " . implode(",\n  ", $parts);
    }

    protected function addColumn($type, $name, $options = [])
    {
        $column = array_merge([
            'type' => $type,
            'name' => $name,
            'nullable' => false,
            'default' => null,
            'unsigned' => false,
            'autoIncrement' => false,
            'length' => null,
            'precision' => null,
            'scale' => null,
            'allowed' => [],
            'comment' => null,
            'after' => null,
        ], $options);

        $this->columns[] = $column;
        return new ColumnDefinition($this, count($this->columns) - 1);
    }

    protected function compileColumn($column)
    {
        $type = $column['type'];
        $name = $column['name'];
        $sql = "`{$name}` ";

        switch ($type) {
            case 'increments':
                $sql .= "INT UNSIGNED AUTO_INCREMENT";
                break;
            case 'bigIncrements':
                $sql .= "BIGINT UNSIGNED AUTO_INCREMENT";
                break;
            case 'string':
                $sql .= "VARCHAR({$column['length']})";
                break;
            case 'char':
                $sql .= "CHAR({$column['length']})";
                break;
            case 'text':
                $sql .= "TEXT";
                break;
            case 'mediumText':
                $sql .= "MEDIUMTEXT";
                break;
            case 'longText':
                $sql .= "LONGTEXT";
                break;
            case 'integer':
                $sql .= $column['unsigned'] ? "INT UNSIGNED" : "INT";
                if ($column['autoIncrement']) {
                    $sql .= " AUTO_INCREMENT";
                }
                break;
            case 'bigInteger':
                $sql .= $column['unsigned'] ? "BIGINT UNSIGNED" : "BIGINT";
                if ($column['autoIncrement']) {
                    $sql .= " AUTO_INCREMENT";
                }
                break;
            case 'tinyInteger':
                $sql .= $column['unsigned'] ? "TINYINT UNSIGNED" : "TINYINT";
                break;
            case 'smallInteger':
                $sql .= $column['unsigned'] ? "SMALLINT UNSIGNED" : "SMALLINT";
                break;
            case 'boolean':
                $sql .= "TINYINT(1)";
                break;
            case 'decimal':
                $sql .= "DECIMAL({$column['precision']}, {$column['scale']})";
                break;
            case 'float':
                $sql .= "FLOAT({$column['precision']}, {$column['scale']})";
                break;
            case 'double':
                $sql .= "DOUBLE({$column['precision']}, {$column['scale']})";
                break;
            case 'date':
                $sql .= "DATE";
                break;
            case 'dateTime':
                $sql .= "DATETIME";
                break;
            case 'time':
                $sql .= "TIME";
                break;
            case 'timestamp':
                $sql .= "TIMESTAMP";
                break;
            case 'binary':
                $sql .= "BLOB";
                break;
            case 'json':
                $sql .= "JSON";
                break;
            case 'jsonb':
                $sql .= "LONGBLOB";
                break;
            case 'enum':
                $allowed = implode("', '", $column['allowed']);
                $sql .= "ENUM('{$allowed}')";
                break;
            default:
                $sql .= "VARCHAR(255)";
        }

        if (!$column['nullable']) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }

        if ($column['default'] !== null) {
            if (is_string($column['default']) && strtoupper($column['default']) !== 'CURRENT_TIMESTAMP') {
                $sql .= " DEFAULT '{$column['default']}'";
            } else {
                $sql .= " DEFAULT {$column['default']}";
            }
        }

        if ($column['comment']) {
            $sql .= " COMMENT '{$column['comment']}'";
        }

        if ($column['after']) {
            $sql .= " AFTER `{$column['after']}`";
        }

        return $sql;
    }
}

class ColumnDefinition
{
    protected $blueprint;
    protected $index;

    public function __construct($blueprint, $index)
    {
        $this->blueprint = $blueprint;
        $this->index = $index;
    }

    public function nullable($value = true)
    {
        $this->blueprint->columns[$this->index]['nullable'] = $value;
        return $this;
    }

    public function defaultValue($value)
    {
        $this->blueprint->columns[$this->index]['default'] = $value;
        return $this;
    }

    public function unsigned($value = true)
    {
        $this->blueprint->columns[$this->index]['unsigned'] = $value;
        return $this;
    }

    public function after($column)
    {
        $this->blueprint->columns[$this->index]['after'] = $column;
        return $this;
    }

    public function comment($comment)
    {
        $this->blueprint->columns[$this->index]['comment'] = $comment;
        return $this;
    }

    public function unique($name = null)
    {
        $this->blueprint->unique($this->blueprint->columns[$this->index]['name'], $name);
        return $this;
    }

    public function index($name = null)
    {
        $this->blueprint->indexes(
            $this->blueprint->columns[$this->index]['name'],
            $name
        );
        return $this;
    }

    public function primary($name = null)
    {
        $this->blueprint->primary(
            $this->blueprint->columns[$this->index]['name'],
            $name
        );
        return $this;
    }
}

class ForeignKey
{
    protected $column;
    protected $references;
    protected $onTable;
    protected $onDelete = 'NO ACTION';
    protected $onUpdate = 'NO ACTION';
    protected $name;

    public function __construct($column)
    {
        $this->column = $column;
    }

    public function references($column)
    {
        $this->references = $column;
        return $this;
    }

    public function on($table)
    {
        $this->onTable = $table;
        return $this;
    }

    public function onDelete($action)
    {
        $this->onDelete = strtoupper($action);
        return $this;
    }

    public function onUpdate($action)
    {
        $this->onUpdate = strtoupper($action);
        return $this;
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function __toString()
    {
        $name = $this->name ? "`{$this->name}` " : '';
        return "CONSTRAINT {$name}FOREIGN KEY (`{$this->column}`) REFERENCES `{$this->onTable}` (`{$this->references}`) ON DELETE {$this->onDelete} ON UPDATE {$this->onUpdate}";
    }
}
