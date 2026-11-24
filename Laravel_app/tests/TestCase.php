<?php

// Configura las pruebas de Laravel y evita migraciones contra bases externas.

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /** Comprueba el aislamiento antes de que RefreshDatabase ejecute migraciones. */
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $config = $app['config'];

        $unsafe = array_keys(array_filter([
            'APP_ENV' => $app->environment() !== 'testing',
            'DB_CONNECTION' => $config->get('database.default') !== 'sqlite',
            'DB_DATABASE' => $config->get('database.connections.sqlite.database') !== ':memory:',
            'DB_URL' => (bool) $config->get('database.connections.sqlite.url'),
            'DB_FOREIGN_KEYS' => ! $config->get('database.connections.sqlite.foreign_key_constraints'),
            'CACHE_STORE' => $config->get('cache.default') !== 'array',
            'MAIL_MAILER' => $config->get('mail.default') !== 'array',
            'QUEUE_CONNECTION' => $config->get('queue.default') !== 'sync',
            'SESSION_DRIVER' => $config->get('session.driver') !== 'array',
            'BROADCAST_CONNECTION' => ! in_array($config->get('broadcasting.default'), [null, 'null'], true),
        ]));

        if ($unsafe !== []) {
            throw new RuntimeException('Configuración de pruebas no aislada: '.implode(', ', $unsafe));
        }

        return $app;
    }
}
