<?php

use CoreBundle\Test\Flow\Calculate\Strategy\NumberCorrectAnswers;

class NumberCorrectAnswersTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers NumberCorrectAnswers::processData
     * @covers TotalWeight::processData()
     */
    public function testProcessData()
    {
        $data = array(
            '1' => 1,
            '2' => 1,
            '3' => 0,
            '4'=> 0,
            '5' => array(1, 0, 1),
            '6' => 1
        );
        $strategy = new NumberCorrectAnswers();
        $number = $strategy->processData($data);
        $this->assertEquals(5, $number);

    }
}