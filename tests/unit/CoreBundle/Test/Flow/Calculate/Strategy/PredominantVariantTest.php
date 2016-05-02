<?php

use CoreBundle\Test\Flow\Calculate\Strategy\PredominantVariant;

class PredominantVariantTest extends PhpUnit_Framework_TestCase
{
    /**
     * @covers PredominantVariant::processData
     */
    public function testProcessData()
    {
        $data = array(
            1 => '1',
            2 => '2',
            3 => '1',
            4 => '3',
            5 => '1'
        );

        $strategy = new PredominantVariant();
        $variant = $strategy->processData($data);

        $this->assertEquals(1,$variant);
    }
}