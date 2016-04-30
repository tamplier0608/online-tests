<?php

namespace CoreBundle\Auth;

use Corebundle\Auth\Adapter\AdapterInterface;

/**
 * Class Auth
 * @package CoreBundle\Auth
 */
class Auth
{
    protected $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function authorize($username, $password, $salt = '')
    {
        return $this->adapter->authenticate($username, $password, $salt);
    }
}