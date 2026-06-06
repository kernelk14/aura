<?php

namespace AuraCore;

use PDO;

class Schema
{
    protected static $pdo;
    protected static $driver;

    public static function setPdo($pdo)
    {
        self::$pdo = $pdo;
        if ($pdo instanceof PDO) {
            self::$driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        } else {
            self::$driver = null;
        }
    }

    public static function getPdo()
    {
        if (!self::$pdo) {
            $db = new Database();
            self::$pdo = $db->getPdo();
            self::$driver = $db->getDriver();
        }
        return self::$pdo;
    }

    public static function getDriver()
    {
        if (self::$driver === null) {
            self::getPdo();
        }
        return self::$driver;
    }

    public static function create($table, $callback)
    {
        $blueprint = new Blueprint($table, self::getDriver());
        $callback($blueprint);
        $sql = $blueprint->toCreateSql();
        self::getPdo()->exec($sql);
    }

    public static function table($table, $callback)
    {
        $blueprint = new Blueprint($table, self::getDriver());
        $callback($blueprint);
        $sql = $blueprint->toAlterSql();
        if ($sql !== '') {
            self::getPdo()->exec($sql);
        }
    }

    public static function drop($table)
    {
        $wrapped = self::wrapTable($table);
        self::getPdo()->exec("DROP TABLE IF EXISTS {$wrapped}");
    }

    public static function dropIfExists($table)
    {
        self::drop($table);
    }

    public static function dropAllTables()
    {
        $pdo = self::getPdo();
        $driver = self::getDriver();

        $pdo->beginTransaction();
        try {
            if ($driver === 'sqlite') {
                $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif ($driver === 'pgsql') {
                $stmt = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } elseif ($driver === 'sqlsrv' || $driver === 'mssql') {
                $stmt = $pdo->query("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE'");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $stmt = $pdo->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            foreach ($tables as $table) {
                $pdo->exec("DROP TABLE IF EXISTS " . self::wrapTable($table));
            }

            $pdo->commit();
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function hasTable($table)
    {
        $pdo = self::getPdo();
        $driver = self::getDriver();
        $table = (string) $table;

        try {
            if ($driver === 'sqlite') {
                $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
                $stmt->execute([$table]);
            } elseif ($driver === 'pgsql') {
                $stmt = $pdo->prepare("SELECT to_regclass(?) AS tbl");
                $stmt->execute([$table]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                return !empty($row['tbl']);
            } elseif ($driver === 'sqlsrv' || $driver === 'mssql') {
                $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = ?");
                $stmt->execute([$table]);
            } else {
                $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
                $stmt->execute([$table]);
            }
            return $stmt->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function hasColumn($table, $column)
    {
        $pdo = self::getPdo();
        $driver = self::getDriver();
        $table = (string) $table;
        $column = (string) $column;

        try {
            if ($driver === 'sqlite') {
                $stmt = $pdo->prepare("PRAGMA table_info(" . self::wrapTable($table) . ")");
                $stmt->execute();
                $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($columns as $col) {
                    if (($col['name'] ?? null) === $column) {
                        return true;
                    }
                }
                return false;
            } elseif ($driver === 'pgsql') {
                $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_name = ? AND column_name = ?");
                $stmt->execute([$table, $column]);
            } elseif ($driver === 'sqlsrv' || $driver === 'mssql') {
                $stmt = $pdo->prepare("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND COLUMN_NAME = ?");
                $stmt->execute([$table, $column]);
            } else {
                $stmt = $pdo->prepare("SHOW COLUMNS FROM " . self::wrapTable($table) . " LIKE ?");
                $stmt->execute([$column]);
            }
            return $stmt->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function rename($from, $to)
    {
        $pdo = self::getPdo();
        $driver = self::getDriver();
        if ($driver === 'sqlite') {
            $pdo->exec("ALTER TABLE " . self::wrapTable($from) . " RENAME TO " . self::wrapTable($to));
        } elseif ($driver === 'pgsql') {
            $pdo->exec("ALTER TABLE " . self::wrapTable($from) . " RENAME TO " . self::wrapTable($to));
        } elseif ($driver === 'sqlsrv' || $driver === 'mssql') {
            $pdo->exec("EXEC sp_rename " . $pdo->quote($from) . ", " . $pdo->quote($to));
        } else {
            $pdo->exec("RENAME TABLE " . self::wrapTable($from) . " TO " . self::wrapTable($to));
        }
    }

    public static function wrapTable($table)
    {
        $driver = self::getDriver();
        if ($driver === 'sqlsrv' || $driver === 'mssql') {
            return '[' . str_replace(']', ']]', $table) . ']';
        }
        return '"' . str_replace('"', '""', $table) . '"';
    }

    public static function dropColumn($table, $column)
    {
        $wrappedTable = self::wrapTable($table);
        $wrappedCol = '"' . str_replace('"', '""', $column) . '"';
        $pdo = self::getPdo();
        $driver = self::getDriver();

        if ($driver === 'sqlite') {
            $pdo->exec("ALTER TABLE {$wrappedTable} DROP COLUMN {$wrappedCol}");
        } elseif ($driver === 'pgsql') {
            $pdo->exec("ALTER TABLE {$wrappedTable} DROP COLUMN {$wrappedCol}");
        } elseif ($driver === 'sqlsrv' || $driver === 'mssql') {
            $pdo->exec("ALTER TABLE {$wrappedTable} DROP COLUMN [{$column}]");
        } else {
            $pdo->exec("ALTER TABLE {$wrappedTable} DROP COLUMN {$wrappedCol}");
        }
    }
}

class Blueprint
{
    public $table;
    public $columns = [];
    public $commands = [];
    protected $driver = 'mysql';
    protected $engine = 'InnoDB';
    protected $charset = 'utf8mb4';
    protected $collation = 'utf8mb4_unicode_ci';
    protected $temporary = false;
    protected $ifNotExists = false;

    public function __construct($table, $driver = 'mysql')
    {
        $this->table = $table;
        $this->driver = $driver;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function temporary($value = true)
    {
        $this->temporary = $value;
        return $this;
    }

    public function ifNotExists($value = true)
    {
        $this->ifNotExists = $value;
        return $this;
    }

    public function increments($column)
    {
        return $this->addColumn('increments', $column);
    }

    public function bigIncrements($column)
    {
        return $this->addColumn('bigIncrements', $column);
    }

    public function id($column = 'id')
    {
        return $this->bigIncrements($column);
    }

    public function string($column, $length = 255)
    {
        return $this->addColumn('string', $column, ['length' => (int) $length]);
    }

    public function char($column, $length = 255)
    {
        return $this->addColumn('char', $column, ['length' => (int) $length]);
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

    public function tinyText($column)
    {
        return $this->addColumn('tinyText', $column);
    }

    public function integer($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('integer', $column, ['autoIncrement' => (bool) $autoIncrement, 'unsigned' => (bool) $unsigned]);
    }

    public function bigInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('bigInteger', $column, ['autoIncrement' => (bool) $autoIncrement, 'unsigned' => (bool) $unsigned]);
    }

    public function tinyInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('tinyInteger', $column, ['autoIncrement' => (bool) $autoIncrement, 'unsigned' => (bool) $unsigned]);
    }

    public function smallInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('smallInteger', $column, ['autoIncrement' => (bool) $autoIncrement, 'unsigned' => (bool) $unsigned]);
    }

    public function mediumInteger($column, $autoIncrement = false, $unsigned = false)
    {
        return $this->addColumn('mediumInteger', $column, ['autoIncrement' => (bool) $autoIncrement, 'unsigned' => (bool) $unsigned]);
    }

    public function unsignedInteger($column, $autoIncrement = false)
    {
        return $this->integer($column, $autoIncrement, true);
    }

    public function unsignedBigInteger($column, $autoIncrement = false)
    {
        return $this->bigInteger($column, $autoIncrement, true);
    }

    public function boolean($column)
    {
        return $this->addColumn('boolean', $column);
    }

    public function decimal($column, $precision = 8, $scale = 2)
    {
        return $this->addColumn('decimal', $column, ['precision' => (int) $precision, 'scale' => (int) $scale]);
    }

    public function float($column, $precision = 8, $scale = 2)
    {
        return $this->addColumn('float', $column, ['precision' => (int) $precision, 'scale' => (int) $scale]);
    }

    public function double($column, $precision = 15, $scale = 8)
    {
        return $this->addColumn('double', $column, ['precision' => (int) $precision, 'scale' => (int) $scale]);
    }

    public function date($column)
    {
        return $this->addColumn('date', $column);
    }

    public function dateTime($column, $precision = 0)
    {
        return $this->addColumn('dateTime', $column, ['precision' => (int) $precision]);
    }

    public function time($column)
    {
        return $this->addColumn('time', $column);
    }

    public function timestamp($column, $precision = 0)
    {
        return $this->addColumn('timestamp', $column, ['precision' => (int) $precision, 'default' => null]);
    }

    public function timestamps($precision = 0)
    {
        $this->timestamp('created_at', $precision)->nullable();
        $this->timestamp('updated_at', $precision)->nullable();
    }

    public function softDeletes($column = 'deleted_at', $precision = 0)
    {
        $this->timestamp($column, $precision)->nullable();
    }

    public function softDeletesTz($column = 'deleted_at')
    {
        $this->softDeletes($column);
    }

    public function binary($column)
    {
        return $this->addColumn('binary', $column);
    }

    public function uuid($column = 'uuid')
    {
        return $this->addColumn('uuid', $column);
    }

    public function ulid($column = 'ulid')
    {
        return $this->addColumn('ulid', $column);
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
        return $this->addColumn('enum', $column, ['allowed' => $allowed]);
    }

    public function set($column, array $allowed)
    {
        return $this->addColumn('set', $column, ['allowed' => $allowed]);
    }

    public function ipAddress($column = 'ip_address')
    {
        return $this->string($column, 45);
    }

    public function macAddress($column = 'mac_address')
    {
        return $this->string($column, 17);
    }

    public function year($column)
    {
        return $this->addColumn('year', $column);
    }

    public function morphs($column, $indexName = null)
    {
        $this->string("{$column}_type");
        $this->unsignedBigInteger("{$column}_id");
    }

    public function foreign($column)
    {
        $foreign = new ForeignKey($column, $this->driver);
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
        $columns = is_array($columns) ? $columns : [$columns];
        $this->commands['index'][] = compact('columns', 'name', 'type');
        return $this;
    }

    public function index($columns, $name = null)
    {
        return $this->indexes($columns, $name, 'INDEX');
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

    public function fullText($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        return $this->indexes($columns, $name, 'FULLTEXT');
    }

    public function spatialIndex($columns, $name = null)
    {
        $columns = is_array($columns) ? $columns : [$columns];
        return $this->indexes($columns, $name, 'SPATIAL');
    }

    public function dropColumn($column)
    {
        $columns = is_array($column) ? $column : [$column];
        foreach ($columns as $col) {
            $this->commands['dropColumn'][] = $col;
        }
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

    public function dropFullText($name)
    {
        return $this->dropIndex($name);
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
            $isAutoIncPrimary = !empty($column['autoIncrement'])
                && in_array($column['type'], ['increments', 'bigIncrements'], true);
            if ($isAutoIncPrimary && in_array($this->driver, ['pgsql', 'sqlsrv', 'mssql'], true)) {
                // column already has PRIMARY KEY in its type
            } elseif (in_array($column['type'], ['increments', 'bigIncrements'], true)) {
                // mysql/sqlite put PRIMARY KEY in the column type via compileAutoIncrementColumn
            }
        }

        foreach ($this->commands as $type => $items) {
            switch ($type) {
                case 'index':
                    foreach ($items as $idx) {
                        $indexes[] = $idx;
                    }
                    break;
                case 'foreign':
                    foreach ($items as $fk) {
                        $foreignKeys[] = (string) $fk;
                    }
                    break;
            }
        }

        $all = array_merge($columns, $this->compileIndexes($indexes), $foreignKeys);
        $columnDefs = implode(",\n  ", $all);

        $sql = $this->compileCreateTablePrefix() . " (\n  {$columnDefs}\n)";

        return $sql . $this->compileTableSuffix();
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
                        $parts[] = "ADD " . $this->compileSingleIndex($idx);
                    }
                    break;
                case 'foreign':
                    foreach ($items as $fk) {
                        $parts[] = "ADD " . (string) $fk;
                    }
                    break;
                case 'dropColumn':
                    foreach ($items as $col) {
                        $parts[] = $this->compileDropColumn($col);
                    }
                    break;
                case 'renameColumn':
                    foreach ($items as $rc) {
                        $parts[] = $this->compileRenameColumn($rc['from'], $rc['to']);
                    }
                    break;
                case 'dropPrimary':
                    $parts[] = "DROP PRIMARY KEY";
                    break;
                case 'dropUnique':
                    foreach ($items as $name) {
                        $parts[] = "DROP INDEX " . $this->wrapIndexName($name);
                    }
                    break;
                case 'dropIndex':
                    foreach ($items as $name) {
                        $parts[] = "DROP INDEX " . $this->wrapIndexName($name);
                    }
                    break;
                case 'dropForeign':
                    foreach ($items as $name) {
                        $parts[] = "DROP FOREIGN KEY " . $this->wrapIndexName($name);
                    }
                    break;
            }
        }

        if (empty($parts)) {
            return '';
        }

        return "ALTER TABLE " . $this->wrapTable($this->table) . "\n  " . implode(",\n  ", $parts);
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
            'first' => false,
        ], $options);

        $this->columns[] = $column;
        return new ColumnDefinition($this, count($this->columns) - 1);
    }

    protected function compileCreateTablePrefix()
    {
        $prefix = 'CREATE ';
        if ($this->temporary) {
            $prefix .= $this->driver === 'pgsql' ? 'TEMP ' : 'TEMPORARY ';
        }
        $prefix .= 'TABLE ';

        if ($this->ifNotExists) {
            $prefix .= 'IF NOT EXISTS ';
        }

        return $prefix . $this->wrapTable($this->table);
    }

    protected function compileTableSuffix()
    {
        $parts = [];

        if ($this->driver === 'mysql' && $this->engine) {
            $parts[] = "ENGINE = " . $this->engine;
        }
        if ($this->driver === 'mysql' && $this->charset) {
            $parts[] = "DEFAULT CHARACTER SET = " . $this->charset;
        }
        if ($this->driver === 'mysql' && $this->collation) {
            $parts[] = "DEFAULT COLLATE = " . $this->collation;
        }
        if ($this->driver === 'pgsql' && $this->driver === 'pgsql' && !empty($this->tablespace)) {
            $parts[] = "TABLESPACE = " . $this->tablespace;
        }
        if ($this->driver === 'sqlite' && $this->temporary) {
            $parts[] = "ON COMMIT DROP";
        }

        if (empty($parts)) {
            return '';
        }

        return "\n" . implode("\n", $parts);
    }

    protected function compileIndexes(array $indexes)
    {
        $compiled = [];
        foreach ($indexes as $idx) {
            $compiled[] = $this->compileSingleIndex($idx);
        }
        return $compiled;
    }

    protected function compileSingleIndex(array $idx)
    {
        $colList = implode(', ', array_map([$this, 'wrapColumn'], $idx['columns']));
        $name = !empty($idx['name']) ? $this->wrapIndexName($idx['name']) . ' ' : '';

        if ($idx['type'] === 'PRIMARY KEY') {
            return "PRIMARY KEY ({$colList})";
        }
        if ($idx['type'] === 'UNIQUE') {
            return "UNIQUE {$name}({$colList})";
        }
        if ($idx['type'] === 'FULLTEXT') {
            if ($this->driver === 'mysql') {
                return "FULLTEXT {$name}({$colList})";
            }
            return "INDEX {$name}({$colList})";
        }
        if ($idx['type'] === 'SPATIAL') {
            if ($this->driver === 'mysql') {
                return "SPATIAL {$name}({$colList})";
            }
        }
        return "INDEX {$name}({$colList})";
    }

    protected function compileColumn($column)
    {
        $type = $column['type'];
        $name = $column['name'];
        $isPrimaryAutoIncrement = in_array($type, ['increments', 'bigIncrements'], true)
            || !empty($column['autoIncrement']);

        $sql = $this->wrapColumn($name) . " ";

        if ($isPrimaryAutoIncrement && in_array($type, ['increments', 'bigIncrements'], true)) {
            $sql .= $this->compileAutoIncrementColumn($type);
        } else {
            $sql .= $this->getColumnType($column);
            if (!empty($column['autoIncrement'])) {
                $sql .= $this->compileAutoIncrement($column);
            }
        }

        if (!$column['nullable']) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }

        if ($column['default'] !== null) {
            $sql .= " " . $this->compileDefault($column['default']);
        }

        if ($column['comment'] !== null) {
            $sql .= " COMMENT '" . addslashes($column['comment']) . "'";
        }

        if (!empty($column['first'])) {
            $sql .= " FIRST";
        } elseif (!empty($column['after']) && $this->driver === 'mysql') {
            $sql .= " AFTER " . $this->wrapColumn($column['after']);
        }

        return $sql;
    }

    protected function compileAutoIncrementColumn($type)
    {
        if ($this->driver === 'sqlite') {
            return 'INTEGER PRIMARY KEY AUTOINCREMENT';
        }
        if ($this->driver === 'pgsql') {
            return $type === 'bigIncrements' ? 'BIGSERIAL PRIMARY KEY' : 'SERIAL PRIMARY KEY';
        }
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return $type === 'bigIncrements' ? 'BIGINT IDENTITY(1,1) PRIMARY KEY' : 'INT IDENTITY(1,1) PRIMARY KEY';
        }
        return $type === 'bigIncrements' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INT UNSIGNED AUTO_INCREMENT PRIMARY KEY';
    }

    protected function getColumnType($column)
    {
        $type = $column['type'];
        $unsigned = !empty($column['unsigned']) && $this->driver !== 'sqlite';

        switch ($type) {
            case 'increments':
                return $this->driver === 'pgsql' ? 'SERIAL' : 'INT UNSIGNED';
            case 'bigIncrements':
                return $this->driver === 'pgsql' ? 'BIGSERIAL' : 'BIGINT UNSIGNED';
            case 'string':
                $len = $column['length'] ?? 255;
                return "VARCHAR({$len})";
            case 'char':
                $len = $column['length'] ?? 255;
                return "CHAR({$len})";
            case 'text':
                return 'TEXT';
            case 'mediumText':
                return 'MEDIUMTEXT';
            case 'longText':
                return $this->driver === 'pgsql' ? 'TEXT' : 'LONGTEXT';
            case 'tinyText':
                return 'TINYTEXT';
            case 'integer':
                return $unsigned ? 'INT UNSIGNED' : 'INT';
            case 'bigInteger':
                return $unsigned ? 'BIGINT UNSIGNED' : 'BIGINT';
            case 'tinyInteger':
                if ($this->driver === 'pgsql') {
                    return $unsigned ? 'SMALLINT' : 'SMALLINT';
                }
                return $unsigned ? 'TINYINT UNSIGNED' : 'TINYINT';
            case 'smallInteger':
                return $unsigned ? 'SMALLINT UNSIGNED' : 'SMALLINT';
            case 'mediumInteger':
                if ($this->driver === 'pgsql') {
                    return $unsigned ? 'INTEGER' : 'INTEGER';
                }
                return $unsigned ? 'MEDIUMINT UNSIGNED' : 'MEDIUMINT';
            case 'boolean':
                if ($this->driver === 'pgsql') {
                    return 'BOOLEAN';
                }
                return 'TINYINT(1)';
            case 'decimal':
                return "DECIMAL({$column['precision']}, {$column['scale']})";
            case 'float':
                return "FLOAT({$column['precision']}, {$column['scale']})";
            case 'double':
                return "DOUBLE({$column['precision']}, {$column['scale']})";
            case 'date':
                return 'DATE';
            case 'dateTime':
                $precision = $column['precision'] ?? 0;
                if ($precision > 0) {
                    return "DATETIME({$precision})";
                }
                return 'DATETIME';
            case 'time':
                return 'TIME';
            case 'timestamp':
                $precision = $column['precision'] ?? 0;
                if ($precision > 0) {
                    return "TIMESTAMP({$precision})";
                }
                return 'TIMESTAMP';
            case 'binary':
                if ($this->driver === 'pgsql') {
                    return 'BYTEA';
                }
                if ($this->driver === 'sqlite') {
                    return 'BLOB';
                }
                return 'BLOB';
            case 'uuid':
                return 'CHAR(36)';
            case 'ulid':
                return 'CHAR(26)';
            case 'json':
                if ($this->driver === 'pgsql') {
                    return 'JSON';
                }
                if ($this->driver === 'sqlite') {
                    return 'TEXT';
                }
                return 'JSON';
            case 'jsonb':
                if ($this->driver === 'pgsql') {
                    return 'JSONB';
                }
                if ($this->driver === 'sqlite') {
                    return 'TEXT';
                }
                if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
                    return 'NVARCHAR(MAX)';
                }
                return 'JSON';
            case 'enum':
                $allowed = array_map([$this, 'escapeEnumValue'], $column['allowed']);
                $list = "'" . implode("', '", $allowed) . "'";
                if ($this->driver === 'pgsql') {
                    return "VARCHAR(255) CHECK (\"{$column['name']}\" IN ({$list}))";
                }
                return "ENUM({$list})";
            case 'set':
                $allowed = array_map([$this, 'escapeEnumValue'], $column['allowed']);
                $list = "'" . implode("', '", $allowed) . "'";
                return "SET({$list})";
            case 'year':
                return $this->driver === 'pgsql' ? 'INTEGER' : 'YEAR';
            default:
                return 'VARCHAR(255)';
        }
    }

    protected function compileAutoIncrement($column)
    {
        $type = $column['type'];
        if ($this->driver === 'pgsql') {
            return '';
        }
        if ($this->driver === 'sqlite') {
            return 'PRIMARY KEY AUTOINCREMENT';
        }
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return 'IDENTITY(1,1)';
        }
        return 'AUTO_INCREMENT';
    }

    protected function compileDefault($default)
    {
        if (is_bool($default)) {
            return 'DEFAULT ' . ($default ? '1' : '0');
        }
        if (is_int($default) || is_float($default)) {
            return "DEFAULT {$default}";
        }
        if (is_string($default)) {
            $upper = strtoupper(trim($default));
            if (in_array($upper, ['CURRENT_TIMESTAMP', 'NOW()', 'NULL'], true)) {
                return "DEFAULT {$upper}";
            }
            return "DEFAULT '" . addslashes($default) . "'";
        }
        if (is_null($default)) {
            return 'DEFAULT NULL';
        }
        return "DEFAULT '" . addslashes((string) $default) . "'";
    }

    protected function compileDropColumn($column)
    {
        $col = $this->wrapColumn($column);
        if ($this->driver === 'sqlite') {
            $pdo = Schema::getPdo();
            $table = $this->table;
            $rows = $pdo->query("PRAGMA table_info(" . Schema::wrapTable($table) . ")")->fetchAll(PDO::FETCH_ASSOC);
            $newColumns = [];
            $oldColumns = [];
            $pk = null;
            foreach ($rows as $row) {
                $oldColumns[] = $row['name'];
                if ($row['pk'] == 1) {
                    $pk = $row['name'];
                }
                if ($row['name'] !== $column) {
                    $newColumns[] = $row['name'] . ' ' . $row['type'];
                }
            }
            if (!$pk) {
                $pk = 'id';
                $newColumns[] = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
            }
            $temp = $table . '__temp_' . uniqid();
            $pdo->exec("CREATE TABLE " . Schema::wrapTable($temp) . " (" . implode(', ', $newColumns) . ")");
            $oldExcept = array_diff($oldColumns, [$column]);
            $oldExceptQuoted = array_map([$this, 'wrapColumn'], $oldExcept);
            $pdo->exec("INSERT INTO " . Schema::wrapTable($temp) . " (" . implode(', ', $oldExceptQuoted) . ") SELECT " . implode(', ', $oldExceptQuoted) . " FROM " . Schema::wrapTable($table));
            $pdo->exec("DROP TABLE " . Schema::wrapTable($table));
            $pdo->exec("ALTER TABLE " . Schema::wrapTable($temp) . " RENAME TO " . Schema::wrapTable($table));
            return '';
        }
        return "DROP COLUMN {$col}";
    }

    protected function compileRenameColumn($from, $to)
    {
        if ($this->driver === 'sqlite') {
            $pdo = Schema::getPdo();
            $table = $this->table;
            $rows = $pdo->query("PRAGMA table_info(" . Schema::wrapTable($table) . ")")->fetchAll(PDO::FETCH_ASSOC);
            $newColumns = [];
            $oldColumns = [];
            $pk = null;
            foreach ($rows as $row) {
                $oldColumns[] = $row['name'];
                if ($row['pk'] == 1) {
                    $pk = $row['name'];
                }
                if ($row['name'] === $from) {
                    $newColumns[] = $row['name'] = $to;
                }
                $newColumns[] = $row['name'] . ' ' . $row['type'];
            }
            if (!$pk) {
                $newColumns[] = 'id INTEGER PRIMARY KEY AUTOINCREMENT';
            }
            $temp = $table . '__temp_' . uniqid();
            $pdo->exec("CREATE TABLE " . Schema::wrapTable($temp) . " (" . implode(', ', $newColumns) . ")");
            $oldExcept = array_filter($oldColumns, function ($c) use ($from) {
                return $c !== $from;
            });
            $oldExcept[] = $to;
            $oldExceptQuoted = array_map([$this, 'wrapColumn'], $oldExcept);
            $pdo->exec("INSERT INTO " . Schema::wrapTable($temp) . " (" . implode(', ', $oldExceptQuoted) . ") SELECT " . implode(', ', $oldExceptQuoted) . " FROM " . Schema::wrapTable($table));
            $pdo->exec("DROP TABLE " . Schema::wrapTable($table));
            $pdo->exec("ALTER TABLE " . Schema::wrapTable($temp) . " RENAME TO " . Schema::wrapTable($table));
            return '';
        }
        if ($this->driver === 'pgsql') {
            return "RENAME COLUMN {$this->wrapColumn($from)} TO {$this->wrapColumn($to)}";
        }
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return "RENAME COLUMN {$this->wrapColumn($from)} TO {$this->wrapColumn($to)}";
        }
        return "RENAME COLUMN {$this->wrapColumn($from)} TO {$this->wrapColumn($to)}";
    }

    protected function wrapColumn($column)
    {
        if (strpos($column, '(') !== false && strpos($column, ')') !== false) {
            return $column;
        }
        if (strpos($column, '.') !== false) {
            $parts = explode('.', $column);
            $last = array_pop($parts);
            return implode('.', $parts) . '.' . $this->wrapColumn($last);
        }
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $column) . ']';
        }
        return '"' . str_replace('"', '""', $column) . '"';
    }

    protected function wrapIndexName($name)
    {
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $name) . ']';
        }
        return '`' . str_replace('`', '``', $name) . '`';
    }

    protected function wrapTable($table)
    {
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $table) . ']';
        }
        return '"' . str_replace('"', '""', $table) . '"';
    }

    protected function escapeEnumValue($value)
    {
        return str_replace("'", "''", (string) $value);
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
        $this->blueprint->columns[$this->index]['nullable'] = (bool) $value;
        return $this;
    }

    public function defaultValue($value)
    {
        $this->blueprint->columns[$this->index]['default'] = $value;
        return $this;
    }

    public function default($value)
    {
        return $this->defaultValue($value);
    }

    public function unsigned($value = true)
    {
        $this->blueprint->columns[$this->index]['unsigned'] = (bool) $value;
        return $this;
    }

    public function autoIncrement($value = true)
    {
        $this->blueprint->columns[$this->index]['autoIncrement'] = (bool) $value;
        return $this;
    }

    public function first()
    {
        $this->blueprint->columns[$this->index]['first'] = true;
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

    public function charset($charset)
    {
        return $this;
    }

    public function collation($collation)
    {
        return $this;
    }

    public function change()
    {
        return $this;
    }

    public function unique($name = null)
    {
        $this->blueprint->unique($this->blueprint->columns[$this->index]['name'], $name);
        return $this;
    }

    public function index($name = null)
    {
        $this->blueprint->index(
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

    public function references($column)
    {
        return $this->blueprint->foreign($this->blueprint->columns[$this->index]['name'])
            ->references($column);
    }
}

class ForeignKey
{
    protected $column;
    protected $references;
    protected $onTable;
    protected $onDelete;
    protected $onUpdate;
    protected $name;
    protected $driver = 'mysql';

    public function __construct($column, $driver = 'mysql')
    {
        $this->column = $column;
        $this->driver = $driver;
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

    public function cascadeOnDelete()
    {
        return $this->onDelete('CASCADE');
    }

    public function cascadeOnUpdate()
    {
        return $this->onUpdate('CASCADE');
    }

    public function nullOnDelete()
    {
        return $this->onDelete('SET NULL');
    }

    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    public function __toString()
    {
        $column = $this->wrapColumn($this->column);
        $references = $this->wrapColumn($this->references ?? 'id');
        $onTable = $this->wrapTable($this->onTable ?? '');

        if ($this->name) {
            $name = $this->wrapName($this->name) . ' ';
            $sql = "CONSTRAINT {$name}FOREIGN KEY ({$column}) REFERENCES {$onTable} ({$references})";
        } else {
            $sql = "FOREIGN KEY ({$column}) REFERENCES {$onTable} ({$references})";
        }

        if ($this->onDelete) {
            $sql .= " ON DELETE {$this->onDelete}";
        }
        if ($this->onUpdate) {
            $sql .= " ON UPDATE {$this->onUpdate}";
        }
        return $sql;
    }

    protected function wrapName($name)
    {
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $name) . ']';
        }
        return '"' . str_replace('"', '""', $name) . '"';
    }

    protected function wrapColumn($column)
    {
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $column) . ']';
        }
        return '"' . str_replace('"', '""', $column) . '"';
    }

    protected function wrapTable($table)
    {
        if ($this->driver === 'sqlsrv' || $this->driver === 'mssql') {
            return '[' . str_replace(']', ']]', $table) . ']';
        }
        return '"' . str_replace('"', '""', $table) . '"';
    }
}
