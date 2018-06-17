<?php
namespace test\library;

use PHPUnit\Framework\TestCase;
use system\library\Filter;

class FilterTest extends TestCase {
    protected $class;

    protected function setUp() {
        parent::setUp();
        $this->class = new Filter('start = :start');
        $this->class->addChunk("a = :a", [':a' => 1]);
        $this->class->addChunk("b LIKE :b", [':b' => 'bbb']);
        $this->class->addChunk("c IS NULL");
        $filter = new Filter();
        $filter->addChunk('d = :d AND (e LIKE :e0 OR e LIKE :e1)', [':d' => 'ddd', ':e0' => 'eee000', ':e1' => 'eee111']);
        $this->class->addChunk($filter);
        $this->class->addChunk('');
    }

    /**
     * @test
     */
    public function getFilterString() {
        $expected = "start = :start AND a = :a AND b LIKE :b AND c IS NULL AND d = :d AND (e LIKE :e0 OR e LIKE :e1)";
        $this->assertEquals($expected, $this->class->getFilterString());
    }

    /**
     * @test
     */
    public function getParams() {
        $expected = [':a' => 1, ':b' => 'bbb', ':d' => 'ddd', ':e0' => 'eee000', ':e1' => 'eee111'];
        $this->assertEquals($expected, $this->class->getParams());
    }
}
 