<?php

namespace CoreBundle\Db\Adapter;

use CoreBundle\Db\Connection as DbConnection;
use CoreBundle\Db\Error;
use Doctrine\DBAL\Driver\Connection;

class DoctrineDbal implements DbConnection
{
    protected $connection;
    protected $lastError;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function exec($query, array $params = array())
    {
        $sth = $this->connection->executeQuery($query, $params);

        $errorInfo = $sth->errorInfo();

        if ($errorInfo[0] != 00000) {
            $this->lastError(new Error($errorInfo[1], $errorInfo[2]));
            return false;
        }

        return $sth;
    }

    public function getError()
    {
        return $this->lastError;
    }

    public function quote($input)
    {
        return $this->connection->quote($input);
    }
}