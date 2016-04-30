<?php

namespace CoreBundle\Auth\Adapter;

/**
 * Interface AdapterInterface
 * @package CoreBundle\Auth\Adapter
 */
interface AdapterInterface
{
    public function authenticate($username, $password);
}