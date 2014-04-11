<?php

namespace Mds\Support\Laravel\Facade;

use PHPUnit_Framework_TestCase;

class ColliveryTest extends PHPUnit_Framework_TestCase
{
  /**
  * @test
  */
  public function returns_facade_accessor_key()
  {
    $this->assertEquals(
      'collivery',
      Collivery::getFacadeAccessor()
    );
  }
}
