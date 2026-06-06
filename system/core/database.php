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
    protected $lastAffectedRows = 0;

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
            $this->driver = 'mysql';
        }

        $dsn = $this->buildDsn($driver);
        $username = $this->config['username'] ?? null;
        $password = $this->config['password'] ?? null;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($driver === 'mysql') {
            $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . ($this->config['charset'] ?? 'utf8');
        }

        try {
            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \RuntimeException(
                "Database connection failed for driver [{$driver}]: " . $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }
    }

    protected function buildDsn($driver)
    {
        $config = $this->config;

        switch ($driver) {
            case 'mysql':
                $host = $config['host'] ?? '127.0.0.1';
                $dbname = $config['database'] ?? '';
                $charset = $config['charset'] ?? 'utf8mb4';
                $port = $config['port'] ?? 3306;
                $unixSocket = $config['unix_socket'] ?? null;
                if ($unixSocket) {
                    return "mysql:unix_socket={$unixSocket};dbname={$dbname};charset={$charset}";
                }
                return "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            case 'pgsql':
                $host = $config['host'] ?? '127.0.0.1';
                $dbname = $config['database'] ?? '';
                $port = $config['port'] ?? 5432;
                $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
                if (!empty($config['sslmode'])) {
                    $dsn .= ";sslmode={$config['sslmode']}";
                }
                return $dsn;

            case 'sqlite':
                $database = $config['database'] ?? ':memory:';
                return "sqlite:" . $database;

            case 'sqlsrv':
                $host = $config['host'] ?? '127.0.0.1';
                $dbname = $config['database'] ?? '';
                $port = $config['port'] ?? 1433;
                $dsn = "sqlsrv:Server={$host},{$port};Database={$dbname}";
                if (!empty($config['encrypt'])) {
                    $dsn .= ";Encrypt=" . ($config['encrypt'] ? 'true' : 'false');
                }
                return $dsn;

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

    public function getDriver()
    {
        return $this->driver;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function table($table)
    {
        $qb = new QueryBuilder($this->pdo, $table);
        $qb->setDatabase($this);
        return $qb;
    }

    public function query($sql, array $bindings = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $this->lastAffectedRows = $stmt->rowCount();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function statement($sql, array $bindings = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $this->lastAffectedRows = $stmt->rowCount();
        return $this->lastAffectedRows;
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
        return (int) $this->lastAffectedRows;
    }

    public function recordAffectedRows($count)
    {
        $this->lastAffectedRows = (int) $count;
        return $this;
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

    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }

    public function raw($value)
    {
        if ($value instanceof RawExpression) {
            return $value;
        }
        return new RawExpression((string) $value);
    }

    public function transaction(\Closure $callback)
    {
        $this->beginTransaction();
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
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

    public function getValue()
    {
        return $this->value;
    }
}
