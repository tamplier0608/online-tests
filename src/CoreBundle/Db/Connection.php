<?php

namespace CoreBundle\Db;

interface Connection
{
    public function exec($query, array $params);
    public function getError();
    public function quote($input);
}