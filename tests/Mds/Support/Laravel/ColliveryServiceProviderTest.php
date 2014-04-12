<?php

namespace Mds\Support\Laravel;

use Mockery;
use PHPUnit_Framework_TestCase;

class ColliveryServiceProviderTest extends PHPUnit_Framework_TestCase
{
  public function tearDown()
  {
    Mockery::close();
  }

  /**
  * @test
  */
  public function registers_collivery_resolver()
  {
    $app_mock = $this->get_app_mock();

    $app_mock
      ->shouldReceive('bind')
      ->atLeast()->once()
      ->with(
        'collivery',
        Mockery::on(function($callback) {
          $mock = Mockery::mock('stdClass');

          $mock
            ->shouldReceive('make')
            ->atLeast()->once()
            ->with('config')
            ->andReturn($mock);

          $mock
            ->shouldReceive('make')
            ->atLeast()->once()
            ->with('cache')
            ->andReturn($mock);

          $mock
            ->shouldReceive('get')
            ->atLeast()->once()
            ->with('collivery::config')
            ->andReturn([]);

          $this->assertInstanceOf(
            'Mds\Collivery',
            $callback($mock)
          );

          return true;
        })
      );

    $provider_mock = $this->get_collivery_service_provider_mock($app_mock);

    $provider_mock->register();
  }

  /**
  * @test
  */
  public function registers_the_collivery_package()
  {
    $mock = $this->get_collivery_service_provider_mock();

    $mock
      ->shouldReceive('package')
      ->atLeast()->once();

    $mock->boot();
  }

  /**
  * @test
  */
  public function provides_nothing()
  {
    $mock = $this->get_collivery_service_provider_mock();

    $this->assertEquals(
      [],
      $mock->provides()
    );
  }

  protected function get_app_mock()
  {
    return Mockery::mock('stdClass');
  }

  protected function get_collivery_service_provider_mock($app_mock = null)
  {
    if ($app_mock === null) {
      $app_mock = $this->get_app_mock();
    }

    return Mockery::mock('Mds\Support\Laravel\ColliveryServiceProvider', [
        $app_mock
      ])
      ->shouldAllowMockingProtectedMethods()
      ->makePartial();
  }
}
