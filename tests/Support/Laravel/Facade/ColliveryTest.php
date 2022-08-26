<?php

namespace Support\Laravel\Facade;

use Mds\Collivery\Support\Laravel\Facade\Collivery;

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
    public function returnsFacadeAccessorKey()
    {
        $this->assertEquals(
            'collivery',
            Collivery::getFacadeAccessor()
        );
    }
}
