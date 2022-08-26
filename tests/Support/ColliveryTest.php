<?php

namespace Mds;

use Mds\Collivery\Collivery;
use PHPUnit\Framework\TestCase;
/**
 * @internal
 * @coversNothing
 */
class ColliveryTest extends TestCase
{
    /**
     * @test
     */
    public function constructsAggressively()
    {
        // TODO need to see if we can split this class up
        // before a ton of tests are written.

        $this->markTestIncomplete(
            'This test has not been implemented yet.'
        );
    }

    public function testWaybillResult()
    {
        $collivery = new Collivery(['user_email' => 'api@collivery.co.za', 'user_password' => 'api123']);
        $result = $collivery->getWaybill(2502420);
        $this->assertTrue(is_array($result), 'The waybill result must be an array');
        $errors = $collivery->getErrors();
        $this->assertTrue(empty($errors), 'An error occurred trying to get the waybill.');

        return $result;
    }

    /**
     * @depends testWaybillResult
     */
    public function testWaybillData(array $result)
    {
        $this->assertTrue(!empty($result['file']), 'Waybill can not be empty');
        $this->assertTrue(strlen($result['file']) > 0, 'PDF file empty');
    }
}
