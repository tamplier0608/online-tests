<?php

namespace AppBundle\Entity;

use CoreBundle\Db\Entity;

class Test extends Entity
{
    protected static $table = 'tests';

    public function isFree()
    {
        return $this->price == 0;
    }
}