# Yii 2 Deep Instantiate

![Latest Stable Version](https://img.shields.io/packagist/v/bizley/deep-instantiate.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/bizley/deep-instantiate.svg)](https://packagist.org/packages/bizley/deep-instantiate)
![License](https://img.shields.io/packagist/l/bizley/deep-instantiate.svg)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fbizley%2Fyii2-deep-instantiate%2Fmain)](https://dashboard.stryker-mutator.io/reports/github.com/bizley/yii2-deep-instantiate/main)

This package provides Yii 2 Dependency Injector Container capable of automatically resolving nested constructor interface-typed dependencies.

### Requirements

- Yii 2.0.39.3+
- PHP 7.0+

### Installation

```
composer require bizley/deep-instantiate:^1.0
```

### Usage

Directly - just call `new \Bizley\DeepInstantiate\Container()`.  
Globally - set `\Yii::$container = new \Bizley\DeepInstantiate\Container();` in your entry script.

### Enhanced Instantiating

```php
class Alpha implements AlphaInterface
{
    private $beta;

    public function __construct(BetaInterface $beta)
    {
        $this->beta = $beta;
    }
}

class Beta implements BetaInterface
{
}

class Gamma
{
    private $alpha;

    public function __construct(AlphaInterface $alpha)
    {
        $this->alpha = $alpha;
    }
}
```

With the original Container:

```php
$container->set(BetaInterface::class, Beta::class);
$alpha = $container->get(Alpha::class);
```

With Deep Instantiate Container you just need:

```php
$alpha = $container->get(Alpha::class, [Beta::class]);
```

For nested dependencies with the original Container:

```php
$container->set(AlphaInterface::class, Alpha::class);
$container->set(BetaInterface::class, Beta::class);
$gamma = $container->get(Gamma::class);
```

For nested dependencies with Deep Instantiate Container:

```php
$gamma = $container->get(Gamma::class, [
    [
        'class' => Alpha::class,
        '__construct()' => Beta::class
    ]
]);
```
