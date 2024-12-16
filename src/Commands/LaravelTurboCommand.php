<?php

namespace Emaia\LaravelTurbo\Commands;

use Illuminate\Console\Command;

class LaravelTurboCommand extends Command
{
    public $signature = 'laravel-turbo';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
