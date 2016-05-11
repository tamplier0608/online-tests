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
            '1' => '1-1',
            '2' => '3-1',
            '3' => '2-0',
            '4'=> '1-0',
            '5' => array('1-1', '2-0', '3-1'),
            '6' => '5-1'
        );
        $strategy = new NumberCorrectAnswers();
        $number = $strategy->processData($data);
        $this->assertEquals(5, $number);

    }
}