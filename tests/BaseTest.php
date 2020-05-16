<?php
/**
 * Testing abstract class
 *
 * @package acidjazz\metapi
 * @author kevin olson <acidjazz@gmail.com>
 */

namespace acidjazz\metapi\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Orchestra\Testbench\TestCase;

abstract class BaseTest extends TestCase
{
    use WithFaker;

    protected function getPackageProviders($app)
    {
        return ['acidjazz\metapi\ServiceProvider'];
    }

    public function setUp(): void
    {
        parent::setUp();
    }
    protected function getEnvironmentSetup($app): void
    {
        $app['config']->set('database.default', 'testing');
    }
}


/*
"mockery/mockery": "^1.1",
        "sempro/phpunit-pretty-print": "^1.0",
        "orchestra/testbench": "^4.0",
        "phpunit/phpunit": "^8.3",
        "psy/psysh": "^0.9.9",
        "beyondcode/laravel-dump-server": "^1.3"
*/
