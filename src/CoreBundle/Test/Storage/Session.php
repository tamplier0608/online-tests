<?php

namespace CoreBundle\Test\Storage;

use Symfony\Component\HttpFoundation\Session\Session as SessionObject;

class Session implements StorageInterface
{
    protected $session;

    public function __construct(SessionObject $session)
    {
        $this->session = $session;
    }

    public function save($key, $value)
    {
        $this->session->set($key, $value);
    }

    public function restore($key)
    {
        return $this->session->get($key);
    }

    public function remove($key)
    {
        $this->session->remove($key);
    }
}