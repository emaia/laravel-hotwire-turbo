<?php

use Emaia\LaravelHotwireTurbo\Tests\TestCase;
use Illuminate\Support\Facades\View;

uses(TestCase::class)->in(__DIR__);

/**
 * Create a temporary Blade view file and register its location with the
 * ViewFactory. Returns the view name (without extension or path).
 */
function makeTempBladeView(string $contents): string
{
    $dir = sys_get_temp_dir().'/turbo_test_views';

    if (! is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    View::addLocation($dir);

    $name = 'view_'.uniqid();
    file_put_contents($dir.'/'.$name.'.blade.php', $contents);

    return $name;
}
