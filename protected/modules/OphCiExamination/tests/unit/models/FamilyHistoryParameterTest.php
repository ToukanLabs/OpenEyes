<?php

/**
 * Class FamilyHistoryParameterTest
 */
class FamilyHistoryParameterTest extends CDbTestCase
{
    /**
     * @var $object FamilyHistoryParameter
     */
    protected $object;
    protected $searchProvider;

    protected function setUp()
    {
        parent::setUp();
        $this->object = new FamilyHistoryParameter();
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->object);
    }

    /**
     * @covers FamilyHistoryParameter::getResultSet()
     */
    public function testSearch()
    {
        $this->markTestIncomplete('TODO');
    }
}
