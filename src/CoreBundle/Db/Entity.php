<?php

namespace CoreBundle\Db;

use CoreBundle\Db;
use Doctrine\DBAL\Driver\PDOStatement;

/**
 * Class Row
 * @package CoreBundle\Db
 */
abstract class Entity implements \ArrayAccess
{
    /**
     * @var Table name
     */
    protected static $table;

    /**
     * @var Database adapter object
     */
    protected static $conn;

    /**
     * @var string Primary keys name
     */
    protected static $primaryKey = 'id';

    protected static $avoidSaving = array();

    public function __construct($id = null)
    {
        if (null !== $id) {
            $this->{static::$primaryKey} = $id;
            $rowObject = $this->fetch();

            if ($rowObject) {
                $this->fill($rowObject);
            } else {
                throw new \InvalidArgumentException("Entity with id = $id is not found.");
            }
        }
    }

    /**
     * @return mixed
     */
    protected function fetch()
    {
        $query = $this->buildFetchQuery();
        $sth = self::getDbConnection()->exec($query, array($this->{static::$primaryKey}));

        if (!$sth instanceof PDOStatement) {
            return false;
        }

        return $sth->fetchObject();
    }

    protected function buildFetchQuery()
    {
        $query = 'SELECT * FROM ' . static::$table;
        $query .= ' WHERE ' . static::$primaryKey . ' = ?';

        return $query;
    }

    public static function getDbConnection() {
        if (null === static::$conn) {
            throw new \RuntimeException('Database adapter is not set!');
        }
        return static::$conn;
    }

    /**
     * @param $data
     */
    public function fill($data)
    {
        if (empty($data)) {
            return;
        }

        foreach ($data as $field => $value) {
            $this->{$field} = $value;
        }
    }

    public static function setDefaultDbConnection(Connection $conn)
    {
        static::$conn = $conn;
    }

    /**
     * @param $field
     * @return null
     */
    public function __get($field)
    {
        if (isset($this->{$field})) {
            return $this->{$field};
        }

        return null;
    }

    /**
     * @param $field
     * @param $value
     * @return $this
     */
    public function __set($field, $value)
    {
        $this->{$field} = $value;

        return $this;
    }

    /**
     * @return \stdClass
     */
    public function isEmpty()
    {
        $reflector = new \ReflectionObject($this);
        $vars = $reflector->getProperties(\ReflectionProperty::IS_PUBLIC);

        return empty($vars);
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $query = $this->buildDeleteQuery();
        $sth = static::getDbConnection()->exec($query, array($this->{static::$primaryKey}));

        if (!$sth instanceof PDOStatement) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    protected function buildDeleteQuery()
    {
        $sql = 'DELETE FROM ' . static::$table;
        $sql .= ' WHERE ' . static::$primaryKey . ' = ?';
        return $sql;
    }

    /**
     * Save row
     *
     * @return bool Result of operation
     */
    public function save()
    {
        $sql = $this->buildSaveQuery();

        $params = array_values($this->getPublicVars());
        $params = array_merge($params, $params);

        $result = static::getDbConnection()->exec($sql, $params);

        if (!$result instanceof PDOStatement) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    protected function buildSaveQuery()
    {
        $query = 'INSERT INTO ' . static::$table;

        $values = array();
        $fields = array();
        $params = array();

        foreach ($this->getPublicVars() as $field => $value) {
            $fields[] = $field;
            $params[] = $field . ' = ?';
            $values[] = '?';
        }

        $query .= ' (' . implode(',', $fields) . ') VALUES (' . implode(',', $values) . ') ';
        $query .= 'ON DUPLICATE KEY UPDATE ' . implode(',', $params);

        return $query;
    }

    protected function getPublicVars()
    {
        $me = $this;
        $avoid = static::$avoidSaving;

        $vars = function () use ($me, $avoid) {
            $vars = get_object_vars($me);

            foreach ($vars as $key => $value) {
                if (in_array($key, $avoid)) {
                    unset($vars[$key]);
                }
            }
            return $vars;
        };

        return $vars();
    }

    /**
     * Get last MySQL error
     *
     * @return Error
     */
    public function getLastError()
    {
        return static::getDbConnection()->getError();
    }

    /**
     * @param $fields
     * @return $this
     */
    public function setFields($fields)
    {
        $this->fill($fields);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        $vars = $this->getPublicVars();
        return array_key_exists($offset, $vars);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        $vars = $this->getPublicVars();
        return isset($vars[$offset]) ? $vars[$offset] : null;
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }
}
