<?php
namespace system\library;

use test\system\Test;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class ModelTest extends Test {
    /**
     * @test
     */
    public function buildSimpleFieldFilterEntry() {
//        $expected = new Filter(
//            'a = :a AND b = :b AND c IN (:c0, :c1)',
//            [":a" => 1, ':b' => "bbb", ':c0' => true, ':c1' => false]
//        );
//        $builtFilter = TestModel::getInstance()->buildSimpleFieldFilterEntry('a', 1);
//        $builtFilter->addChunk(TestModel::getInstance()->buildSimpleFieldFilterEntry('b', 'bbb'));
//        $builtFilter->addChunk(TestModel::getInstance()->buildSimpleFieldFilterEntry('c', [true, false]));
//        $builtFilter->addChunk(TestModel::getInstance()->buildSimpleFieldFilterEntry('d', null));
//        $this->assertEquals($expected, $builtFilter);
    }
}