<?php

declare(strict_types=1);

use yii\base\Configurable;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\di\NotInstantiableException;

class Container extends \yii\di\Container
{
    /**
     * @param array<int|string, mixed> $baseDependencies
     * @param array<int|string, mixed> $addedDependencies
     * @return array<int|string, mixed>
     * @throws InvalidConfigException
     */
    private function mergeAndResolveDependencies(array $baseDependencies, array $addedDependencies): array
    {
        foreach ($addedDependencies as $index => $dependency) {
            $newDependency = $dependency;
            if ($baseDependencies[$index] instanceof Instance) {
                $newDependency = Instance::ensure($dependency, $baseDependencies[$index]->id, $this);
            }
            $baseDependencies[$index] = $newDependency;
        }

        return $baseDependencies;
    }

    /**
     * Creates an instance of the specified class.
     * This method will resolve dependencies of the specified class, instantiate them, and inject
     * them into the new instance of the specified class.
     * @param string $class the class name
     * @param array $params constructor parameters
     * @param array $config configurations to be applied to the new instance
     * @return object the newly created instance of the specified class
     * @throws InvalidConfigException
     * @throws NotInstantiableException If resolved to an abstract class or an interface (since 2.0.9)
     * @throws ReflectionException
     */
    protected function build($class, $params, $config)
    {
        /** @var ReflectionClass $reflection */
        list($reflection, $dependencies) = $this->getDependencies($class);

        $addDependencies = [];
        if (isset($config['__construct()'])) {
            $addDependencies = $config['__construct()'];
            unset($config['__construct()']);
        }
        foreach ($params as $index => $param) {
            $addDependencies[$index] = $param;
        }

        $hasStringParameter = false;
        $hasIntParameter = false;
        foreach ($addDependencies as $index => $parameter) {
            if (is_string($index)) {
                $hasStringParameter = true;
                if ($hasIntParameter) {
                    break;
                }
            } else {
                $hasIntParameter = true;
                if ($hasStringParameter) {
                    break;
                }
            }
        }
        if ($hasIntParameter && $hasStringParameter) {
            throw new InvalidConfigException(
                'Dependencies indexed by name and by position in the same array are not allowed.'
            );
        }

        if ($addDependencies && is_int(key($addDependencies))) {
            $dependencies = array_values($dependencies);
            $dependencies = $this->mergeAndResolveDependencies($dependencies, $addDependencies);
        } else {
            $dependencies = $this->mergeAndResolveDependencies($dependencies, $addDependencies);
            $dependencies = array_values($dependencies);
        }

        $dependencies = $this->resolveDependencies($dependencies, $reflection);
        if (!$reflection->isInstantiable()) {
            throw new NotInstantiableException($reflection->name);
        }
        if (empty($config)) {
            return $reflection->newInstanceArgs($dependencies);
        }

        $config = $this->resolveDependencies($config);

        if (!empty($dependencies) && $reflection->implementsInterface(Configurable::class)) {
            // set $config as the last parameter (existing one will be overwritten)
            $dependencies[count($dependencies) - 1] = $config;
            return $reflection->newInstanceArgs($dependencies);
        }

        $object = $reflection->newInstanceArgs($dependencies);
        foreach ($config as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }
}
