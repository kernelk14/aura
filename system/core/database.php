<?php

namespace AuraCore;

use PDO;

require_once __DIR__ . '/query-builder.php';
require_once __DIR__ . '/pagination.php';

class Database extends Base
{
    protected $pdo;
    protected $driver;
    protected $config;

    public function __construct($config = null)
    {
        if ($config === null) {
            $config = $this->config('database.default');
        }

        $this->config = $config;
        $this->driver = $config['driver'] ?? 'mysql';

        $this->connect();
    }

    protected function connect()
    {
        $driver = $this->driver;

        if ($driver === 'mysqli') {
            $driver = 'mysql';
        }

        $dsn = $this->buildDsn($driver);
        $username = $this->config['username'] ?? null;
        $password = $this->config['password'] ?? null;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $this->pdo = new PDO($dsn, $username, $password, $options);
    }

    protected function buildDsn($driver)
    {
        $config = $this->config;

        switch ($driver) {
            case 'mysql':
                $host = $config['host'] ?? '127.0.0.1';
                $dbname = $config['database'] ?? '';
                $charset = $config['charset'] ?? 'utf8';
                $port = $config['port'] ?? 3306;
                return "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            case 'pgsql':
                $host = $config['host'] ?? '127.0.0.1';
                $dbname = $config['database'] ?? '';
                $port = $config['port'] ?? 5432;
                return "pgsql:host={$host};port={$port};dbname={$dbname}";

            case 'sqlite':
                return "sqlite:{$config['database']}";

            case 'sqlsrv':
                $host = $config['host'] ?? '127.0.0.1';
                $dbname = $config['database'] ?? '';
                return "sqlsrv:Server={$host};Database={$dbname}";

            default:
                if (isset($config['dsn'])) {
                    return $config['dsn'];
                }
                throw new \Exception("Unsupported database driver: {$driver}");
        }
    }

    public function getPdo()
    {
        return $this->pdo;
    }

    public function table($table)
    {
        return new QueryBuilder($this->pdo, $table);
    }

    public function query($sql)
    {
        if (func_num_args() > 1) {
            $bindings = array_slice(func_get_args(), 1);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($bindings);
            return $stmt->fetchAll();
        }

        return $this->pdo->query($sql)->fetchAll();
    }

    public function statement($sql, $bindings = [])
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($bindings);
    }

    public function get($table)
    {
        return $this->table($table)->get();
    }

    public function getWhere($table, $where)
    {
        $qb = $this->table($table);
        foreach ($where as $key => $value) {
            $qb->where($key, $value);
        }
        return $qb->get();
    }

    public function insert($table, $data)
    {
        return $this->table($table)->insert($data);
    }

    public function update($table, $data, $where = null)
    {
        $qb = $this->table($table);
        if ($where) {
            foreach ($where as $key => $value) {
                $qb->where($key, $value);
            }
        }
        return $qb->update($data);
    }

    public function delete($table, $where = null)
    {
        $qb = $this->table($table);
        if ($where) {
            foreach ($where as $key => $value) {
                $qb->where($key, $value);
            }
        }
        return $qb->delete();
    }

    public function lastInsertId()
    {
        return (int) $this->pdo->lastInsertId();
    }

    public function affectedRows()
    {
        return $this->pdo->query('SELECT ROW_COUNT()')->fetchColumn();
    }

    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    public function commit()
    {
        return $this->pdo->commit();
    }

    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    public function raw($value)
    {
        return new RawExpression($value);
    }
}

class RawExpression
{
    protected $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString()
    {
        return $this->value;
    }
}
