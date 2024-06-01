<?php

namespace Tests;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Encryption\Encrypter;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Vessel\VesselManager;

abstract class TestCase extends BaseTestCase
{
    use WithWorkbench;

    protected $enablesPackageDiscoveries = true;

    protected function defineRoutes($router): void
    {
        $router->get('/context', fn (): string => VesselManager::getContextId());
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('app.key', 'base64:'.base64_encode(
                Encrypter::generateKey($config['app.cipher'])
            ));
        });
    }
}
