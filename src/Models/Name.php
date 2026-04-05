<?php

namespace Emaia\LaravelHotwireTurbo\Models;

use Illuminate\Support\Str;

final class Name
{
    public string $className;

    public string $classNameWithoutRootNamespace;

    public string $singular;

    public string $plural;

    public string $element;

    /** @var array<string, self> */
    private static array $cache = [];

    /** @var array<int, string> */
    private static array $modelNamespaces = ['App\\Models\\', 'App\\'];

    /**
     * @param  array<int, string>  $namespaces
     */
    public static function setModelNamespaces(array $namespaces): void
    {
        self::$modelNamespaces = $namespaces;
        self::$cache = [];
    }

    public static function forModel(object $model): self
    {
        $class = $model::class;

        return self::$cache[$class] ??= self::build($class);
    }

    private static function build(string $className): self
    {
        $name = new self;

        $name->className = $className;
        $name->classNameWithoutRootNamespace = self::removeRootNamespaces($className);
        $name->singular = (string) Str::of($name->classNameWithoutRootNamespace)->replace('\\', '')->snake();
        $name->plural = Str::plural($name->singular);
        $name->element = (string) Str::of(class_basename($className))->snake();

        return $name;
    }

    private static function removeRootNamespaces(string $className): string
    {
        foreach (self::$modelNamespaces as $rootNs) {
            if (Str::startsWith($className, $rootNs)) {
                return Str::replaceFirst($rootNs, '', $className);
            }
        }

        return class_basename($className);
    }

    private function __construct() {}
}
