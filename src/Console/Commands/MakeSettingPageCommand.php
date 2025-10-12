<?php

namespace Waynelogic\FilamentCms\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;

class MakeSettingPageCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:setting-page {name : The name of the page class} {model : Path to the model class}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create setting page';


    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Setting page';

    /**
     * Full class path
     * @var string
     */
    protected string $modelClass;

    /**
     * Class name
     * @var string
     */
    protected string $modelName;

    public function handle(): void
    {
        $modelInput = $this->argument('model');

        if (! class_exists($modelInput)) {
            $this->error("Class [{$modelInput}] does not exist.");
            return;
        }

        $this->modelClass = $modelInput;

        $this->modelName = class_basename($modelInput);

        parent::handle();
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Filament\Pages';
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the page class'],
            ['model', InputArgument::REQUIRED, 'Path to the model class'],
        ];
    }

    protected function getStub(): string
    {
        return __DIR__ . '/stubs/setting-page.stub';
    }

    protected function buildClass($name): array|string
    {
        $stub = parent::buildClass($name);

        return str_replace(
            ['{{ model }}', '{{ model_path }}', '{{ page_title }}'],
            [$this->modelName, $this->modelClass, $this->modelName],
            $stub
        );
    }
}
