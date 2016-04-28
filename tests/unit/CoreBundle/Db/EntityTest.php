<?php

class EntityTest extends \PHPUnit_Framework_TestCase
{
    private $row;

    public function setUp()
    {
        $this->row = new Page();
        $this->row->title = 'Test title';
        $this->row->created = '2016-04-13 14:42:49';
        $this->row->content = 'Page content';
    }

    /**
     * @covers CoreBundle\Db\Entity::buildSaveQuery()
     */
    public function testBuildSaveQuery()
    {
        $method = $this->makeMethodAccessible($this->row, 'buildSaveQuery');
        $expected = 'INSERT INTO pages (title,created,content) VALUES (?,?,?) ON DUPLICATE KEY UPDATE title = ?,created = ?,content = ?';

        $query = $method->invoke($this->row);

        $this->assertEquals($expected, $query);
    }

    /**
     * @return ReflectionMethod
     */
    protected function makeMethodAccessible($object, $methodName)
    {
        $reflectionObject = new \ReflectionObject($object);
        $method = $reflectionObject->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

    /**
     * @covers Corebundle\Db\Entity::buildDeleteQuery()
     */
    public function testBuildDeleteQuery()
    {
        $method = $this->makeMethodAccessible($this->row, 'buildDeleteQuery');
        $expected = 'DELETE FROM pages WHERE id = ?';

        $query = $method->invoke($this->row);

        $this->assertEquals($expected, $query);
    }

    /**
     * @covers Corebundle\Db\Entity::buildFetchQuery()
     */
    public function testBuildFetchQuery()
    {
        $method = $this->makeMethodAccessible($this->row, 'buildFetchQuery');
        $expected = 'SELECT * FROM pages WHERE id = ?';

        $query = $method->invoke($this->row);

        $this->assertEquals($expected, $query);
    }
}
