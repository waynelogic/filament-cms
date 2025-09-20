<?php

namespace Waynelogic\FilamentCms\Service;

use Filament\Commands\FileGenerators\CustomPageClassGenerator;
use Filament\Pages\Page;
use Filament\Support\Commands\FileGenerators\ClassGenerator;
use Waynelogic\FilamentCms\System\Filament\EditSetting;
use Nette\PhpGenerator\Property;

class CustomSettingPageClassGenerator extends ClassGenerator
{
    final public function __construct(
        protected string $fqn,
    ) {}

    public function getNamespace(): string
    {
        return $this->extractNamespace($this->getFqn());
    }

    /**
     * @return array<string>
     */
    public function getImports(): array
    {
        $extends = $this->getExtends();
        $extendsBasename = class_basename($extends);

        return [
            ...(($extendsBasename === class_basename($this->getFqn())) ? [$extends => "Base{$extendsBasename}"] : [$extends]),
        ];
    }

    public function getBasename(): string
    {
        return class_basename($this->getFqn());
    }

    public function getExtends(): string
    {
        return EditSetting::class;
    }
    public function getFqn(): string
    {
        return $this->fqn;
    }
}
