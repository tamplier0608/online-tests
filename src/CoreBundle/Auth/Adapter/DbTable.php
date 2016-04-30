<?php

namespace CoreBundle\Auth\Adapter;
use CoreBundle\Db\Adapter\DoctrineDbal;
use Doctrine\DBAL\Driver\PDOStatement;

/**
 * Class Db
 * @package CoreBundle\Auth\Adapter
 */
class DbTable implements AdapterInterface
{
    protected $db;
    protected $tableName;
    protected $identityColumn;
    protected $credentialColumn;

    public function __construct($db, $tableName, $identityColumn, $credentialColumn)
    {
        $this->db = $db;
        $this->tableName = $tableName;
        $this->identityColumn = $identityColumn;
        $this->credentialColumn;
    }

    public function authenticate($username, $password, $salt = '')
    {
        $password = md5($password . $salt);

        $sql = <<<SQL
            SELECT (CASE WHEN `password` = ? THEN 1 ELSE 0 END) credential_match 
            FROM `users`
            WHERE `username` = ?
            AND `active` = 1;
SQL;
        $sth = $this->db->exec($sql, array($password, $username));
        if (!$sth instanceof PDOStatement) {
            return false;
        }

        return $this->validateResult($sth->fetch());
    }

    protected function validateResult($result)
    {
        return ($result['credential_match'] > 0);
    }
}