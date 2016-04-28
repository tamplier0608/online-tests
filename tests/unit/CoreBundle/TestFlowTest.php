<?php

class TestFlowTest extends \PHPUnit_Framework_TestCase
{
    private $testFlow;
    
    protected function setUp()
    {
        $session = new \Symfony\Component\HttpFoundation\Session\Session();
        $storage = new \CoreBundle\Test\Storage\Session($session);
        $this->testFlow = new \CoreBundle\Test\Flow($storage);
    }

    protected function tearDown()
    {
    }

    /**
     * @covers \CoreBundle\TestFlow::initTestData()
     */
    public function testInitTestData()
    {
        $this->testFlow->initTestData(1);
        var_dump($_SESSION);
        $this->assertArrayHasKey('test-1', $_SESSION);
    }
}
