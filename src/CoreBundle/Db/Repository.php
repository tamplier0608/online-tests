<?php

namespace CoreBundle\Db;
use Doctrine\DBAL\Driver\PDOStatement;

/**
 * Class Repository
 * @package CoreBundle\Db
 */
abstract class Repository
{
    protected static $table;
    protected static $primaryKey = 'id';
    protected static $rowClass;

    protected static $conn;

    public static function beginTransaction()
    {
        static::getDbConnection()->exec('BEGIN;');
    }

    public static function commitTransaction()
    {
        static::getDbConnection()->exec('COMMIT');
    }

    public static function rollbackTransaction()
    {
        static::getDbConnection()->exec('ROLLBACK;');
    }

    public static function setDefaultDbConnection(Connection $conn)
    {
        static::$conn = $conn;
    }

    public static function getDbConnection() {
        if (null === static::$conn) {
            throw new \RuntimeException('Database adapter is not set!');
        }
        return static::$conn;
    }

    /**
     * @param $sql
     * @return mixed
     */
    protected function executeQuery($sql, $params = array())
    {
        $sth = static::getDbConnection()->exec($sql, $params);

        if (!$sth instanceof PDOStatement) {
            throw new \RuntimeException("Error in SQL statement: '$sql'. " . __CLASS__ );
        }
        return $sth;
    }

    /**
     * @param bool|string $limit
     * @return mixed
     */
    public function fetchAll($limit = false, $orderBy = false)
    {
        $sql = $this->buildFetchAllQuery($limit, $orderBy);
        $sth = $this->executeQuery($sql);
        $rowSet = $this->fetchResultInRowset($sth);

        return $rowSet;
    }

    /**
     * @param $id
     * @return Core/Db/Entity
     */
    public function find($id)
    {
        $query = 'SELECT * FROM ' . static::$table . ' WHERE ' . static::$primaryKey . ' = ?';
        $sth = $this->executeQuery($query, array($id));

        return $sth->fetchObject(static::$rowClass);
    }

    public function countAll(array $conditions = array(), array $params = array())
    {
        $query = 'SELECT COUNT(*) as count FROM ' . static::$table;
        $query = $this->prepareConditions($conditions, $query);

        return $this->executeQuery($query, $params)->fetch()['count'];
    }

    public function findBy(array $conditions = array(), array $params = array(), $limit = false, $orderBy = false)
    {
        $query = $this->buildFetchAllQuery();

        $query = $this->prepareConditions($conditions, $query);

        if (false !== $orderBy) {
            $query .= ' ORDER BY ' . $orderBy;
        }

        if (false !== $limit) {
            $query .= ' LIMIT ' . $limit;
        }

        $sth = $this->executeQuery($query, $params);

        return $this->fetchResultInRowset($sth);
    }

    /**
     * @param string $limit
     * @param string $orderBy
     * @return mixed
     */
    protected function buildFetchAllQuery($limit = false, $orderBy = false)
    {
        $sql = 'SELECT * FROM `' . static::$table . '`';

        if ($orderBy) {
            $sql .= ' ORDER BY ' . $orderBy;
        }

        if (false !== $limit) {
            $sql .= ' LIMIT ' . $limit;
        }
        return $sql;
    }

    /**
     * @param array $conditions
     * @param $query
     * @return string
     */
    protected function prepareConditions(array $conditions, $query)
    {
        $countConditions = count($conditions);

        if ($countConditions) {
            for ($i = 0; $i < $countConditions; $i++) {
                if ($i === 0) {
                    $query .= ' WHERE ';
                } else {
                    $query .= ' AND ';
                }
                $query .= $conditions[$i];
            }
            return $query;
        }
        return $query;
    }

    /**
     * @param $sth
     * @return array
     */
    protected function fetchResultInRowset($sth)
    {
        $rowSet = array();

        while ($row = $sth->fetchObject(static::$rowClass)) {
            $rowSet[] = $row;
        }
        return $rowSet;
    }
}