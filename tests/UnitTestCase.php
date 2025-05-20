<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Oltrematica\ParkingHub\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class UnitTestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Oltrematica\\ParkingHub\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        //        config()->set('oltrematica-ParkingHub.prefix', 'custom-prefix');
        //        config()->set('oltrematica-ParkingHub.protected', true);

        /*
        $migration = include __DIR__.'/../database/migrations/create_ParkingHub_table.php.stub';
        $migration->up();
        */
    }

    protected function getPackageProviders($app)
    {
        return [
            //            ServiceProvider::class,
        ];
    }
}
