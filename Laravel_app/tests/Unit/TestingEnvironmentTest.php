<?php

// Comprueba que PHPUnit no herede el entorno de ejecución del contenedor.

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class TestingEnvironmentTest extends TestCase
{
    /** Ejecuta una prueba Laravel en un proceso separado con las variables indicadas. */
    private function runExample(array $environment): Process
    {
        $root = dirname(__DIR__, 2);
        $process = new Process([
            PHP_BINARY,
            $root.'/vendor/bin/phpunit',
            '--configuration',1
            $root.'/phpunit.xml',
            '--do-not-cache-result',
            $root.'/tests/Feature/ExampleTest.php',
        ], $root, $environment);

        $process->run();

        return $process;
    }

    /** Fuerza testing cuando el contenedor hereda APP_ENV=local. */
    public function test_phpunit_overrides_inherited_application_environment(): void
    {
        $process = $this->runExample(['APP_ENV' => 'local']);

        $this->assertSame(0, $process->getExitCode(), $process->getOutput().$process->getErrorOutput());
    }

    /** Impide ejecutar pruebas si una URL de base de datos externa sigue activa. */
    public function test_inherited_database_url_is_rejected(): void
    {
        $process = $this->runExample([
            'APP_ENV' => 'local',
            'DB_URL' => 'sqlite:///tmp/unwanted-test-database.sqlite',
        ]);

        $this->assertNotSame(0, $process->getExitCode());
        $this->assertStringContainsString('Configuración de pruebas no aislada: DB_URL', $process->getOutput());
    }
}
