<?php

namespace Waynelogic\FilamentCms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeSettingCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:setting {name} {--code= : The code/group for the setting (default: snake_case of name)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle() : void
    {
        $name = $this->argument('name');
        $code = $this->option('code') ?: Str::snake($name);

        // Creating model
        $this->info("Creating setting model [{$name}] with code [{$code}]...");
        $exitCode = $this->call('make:setting-model', [
            'name' => $name,
            'code'  => $code,
        ]);

        if ($exitCode !== 0) {
            $this->error('Failed to create setting model.');
            return;
        }

        // Setting full page to model
        $modelClass = 'App\\Models\\Settings\\' . $name;

        // Checking that class exists
        if (! class_exists($modelClass)) {
            $this->warn("Model [{$modelClass}] was not found. Skipping page creation.");
            return;
        }

        // Creating page
        $pageName = $name . 'Page';
        $this->info("Creating setting page [{$pageName}] for model [{$modelClass}]...");
        $exitCode = $this->call('make:setting-page', [
            'name'  => $pageName,
            'model' => $modelClass,
        ]);

        if ($exitCode !== 0) {
            $this->error('Failed to create setting page.');
            return;
        }

        $this->info("✅ Setting [{$name}] created successfully!");
    }
}
