<?php

namespace Tests;

use Illuminate\Foundation\Application;
use LogicException;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        // Se ejecuta antes de que RefreshDatabase pueda migrar.
        if ($app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new LogicException('Las pruebas requieren SQLite :memory:. Se bloqueó cualquier otra base.');
        }

        return $app;
    }
}
