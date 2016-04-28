<?php

namespace AppBundle\Entity\Repository\Test;

use CoreBundle\Db\Repository;

class Results extends Repository
{
    protected static $table = 'test_results';
    protected static $rowClass = 'AppBundle\Entity\Test\Result';

    public function getDescriptionByPoints($testId, $points)
    {
        return $this->findBy(
            array('test_id = ?', 'min_points <= ?', 'max_points >= ?'),
            array($testId, $points, $points)
        )[0];
    }
}