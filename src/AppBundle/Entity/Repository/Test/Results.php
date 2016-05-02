<?php

namespace AppBundle\Entity\Repository\Test;

use CoreBundle\Db\Repository;

class Results extends Repository
{
    protected static $table = 'test_results';
    protected static $rowClass = 'AppBundle\Entity\Test\Result';

    public function getResultByPoints($testId, $points)
    {
        $result = $this->findBy(
            array('test_id = ?', 'min_points <= ?', 'max_points >= ?'),
            array($testId, $points, $points)
        );

        return $result ? $result[0] : false;
    }

    public function getResultByVariant($testId, $variant)
    {
        $result = $this->findBy(
            array('test_id = ?', 'variant = ?'),
            array($testId, $variant)
        );

        return $result ? $result[0] : false;
    }
}