<?php

namespace Waynelogic\FilamentCms\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class MakeSettingModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:setting-model {name : The name of the form class} {code : The code of setting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create setting model';


    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Setting model';


    protected string $code;

    public function handle(): void
    {
        $this->code = $this->argument('code') ?? Str::snake($this->argument('name'));

        parent::handle();
    }

    protected function buildClass($name): array|string
    {
        $stub = parent::buildClass($name);

        // Perpacing code in stub
        return str_replace('{{ code }}', $this->code, $stub);
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Models\Settings';
    }
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the form class'],
            ['code', InputArgument::OPTIONAL, 'The code of setting'],
        ];
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/setting-model.stub';
    }
}
