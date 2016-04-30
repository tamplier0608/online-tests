<?php

namespace CoreBundle\Test\Storage;

interface StorageInterface
{
    public function save($key, $value);
    public function restore($key);
}