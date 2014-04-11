<?php

namespace Mds;

use Mockery;
use PHPUnit_Framework_TestCase;

function time()
{
  // we simulate this method to control the output specifically
  return 15;
}

class CacheTest extends PHPUnit_Framework_TestCase
{
  public function tearDown()
  {
    Mockery::close();
  }

  /**
  * @test
  */
  public function load_reads_config_file()
  {
    // TODO need to see if we can split the low-level and high-level aspects
    // of this class before this test is written.

    $this->markTestIncomplete(
      "This test has not been implemented yet."
    );
  }

  /**
  * @test
  */
  public function has_returns_boolean_based_on_key_presence_and_expiry()
  {
    $cache_mock = $this->get_cache_mock();

    $cache_mock
      ->shouldReceive('load')
      ->atLeast()->once()
      ->with('foo')
      ->andReturn(null);

    $cache_mock
      ->shouldReceive('load')
      ->atLeast()->once()
      ->with('bar')
      ->andReturn([
        'valid' => 60
      ]);

    $cache_mock
      ->shouldReceive('load')
      ->atLeast()->once()
      ->with('baz')
      ->andReturn([
        'valid' => 40
      ]);

    $this->assertFalse(
        $cache_mock->has('foo')
    );

    $this->assertTrue(
        $cache_mock->has('bar')
    );

    $this->assertFalse(
        $cache_mock->has('baz')
    );
  }

  /**
  * @test
  */
  public function get_returns_value_based_on_key_presence_and_expiry()
  {
    $cache_mock = $this->get_cache_mock();

    $cache_mock
      ->shouldReceive('load')
      ->atLeast()->once()
      ->with('foo')
      ->andReturn(null);

    $cache_mock
      ->shouldReceive('load')
      ->atLeast()->once()
      ->with('bar')
      ->andReturn([
        'valid' => 15
      ]);

    $cache_mock
      ->shouldReceive('load')
      ->atLeast()->once()
      ->with('baz')
      ->andReturn([
        'valid' => 45,
        'value' => 'mocked value'
      ]);

    $this->assertNull(
      $cache_mock->get('foo')
    );

    $this->assertNull(
      $cache_mock->get('bar')
    );

    $this->assertEquals(
      'mocked value',
      $cache_mock->get('baz')
    );
  }

  /**
  * @test
  */
  public function put_stores_value()
  {
    // TODO need to see if we can split the low-level and high-level aspects
    // of this class before this test is written.

    $this->markTestIncomplete(
      "This test has not been implemented yet."
    );
  }

  /**
  * @test
  */
  public function forget_removes_value()
  {
    // TODO need to see if we can split the low-level and high-level aspects
    // of this class before this test is written.

    $this->markTestIncomplete(
      "This test has not been implemented yet."
    );
  }

  protected function get_cache_mock()
  {
    return Mockery::mock('Mds\Cache')
      ->shouldAllowMockingProtectedMethods()
      ->makePartial();
  }
}
