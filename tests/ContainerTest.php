<?php

declare(strict_types=1);

namespace Bizley\Tests;

use Bizley\DeepInstantiate\Container;
use Bizley\Tests\Models\A;
use Bizley\Tests\Models\B;
use Bizley\Tests\Models\C;
use Bizley\Tests\Models\D;
use Bizley\Tests\Models\E;
use Bizley\Tests\Models\F;
use Bizley\Tests\YiiModels\Alpha;
use Bizley\Tests\YiiModels\Bar;
use Bizley\Tests\YiiModels\Car;
use Bizley\Tests\YiiModels\Foo;
use Bizley\Tests\YiiModels\Qux;
use PHPUnit\Framework\TestCase;
use yii\di\Instance;

class ContainerTest extends TestCase
{
    public function testInterfaceTypedArgumentIntKey()
    {
        $bar = (new Container())->get(Bar::class, [Qux::class]);
        self::assertInstanceOf(Qux::class, $bar->qux);
    }

    public function testInterfaceTypedArgumentStringKey()
    {
        $bar = (new Container())->get(Bar::class, ['qux' => Qux::class]);
        self::assertInstanceOf(Qux::class, $bar->qux);
    }

    public function testInterfaceTypedOptionalArgumentStringKey()
    {
        $alpha = (new Container())->get(Alpha::class, ['omega' => Qux::class]);
        self::assertInstanceOf(Qux::class, $alpha->omega);
    }

    public function testNonTypedArgumentStringKey()
    {
        $car = (new Container())->get(Car::class, ['color' => Qux::class]);
        self::assertSame(Qux::class, $car->color);
    }

    public function testClassTypedArgumentStringKeyNestedDependencyResolving()
    {
        $foo = (new Container())->get(
            Foo::class,
            [
                'bar' => [
                    'class' => Bar::class,
                    '__construct()' => [Qux::class],
                ]
            ]
        );
        self::assertInstanceOf(Bar::class, $foo->bar);
        self::assertInstanceOf(Qux::class, $foo->bar->qux);
    }

    public function testInterfaceTypedArgumentIntKeyDependencyPreset()
    {
        $c = new Container();
        $c->set('Bizley\Tests\YiiModels\QuxInterface', Qux::class);
        $bar = $c->get(Bar::class, [Qux::class]);
        self::assertInstanceOf(Qux::class, $bar->qux);
    }

    public function testInterfaceTypedArgumentIntKeyDependencyBuilt()
    {
        $bar = (new Container())->get(Bar::class, [new Qux()]);
        self::assertInstanceOf(Qux::class, $bar->qux);
    }

    public function testInterfaceTypedArgumentIntKeyDependencyInstanceOf()
    {
        $bar = (new Container())->get(Bar::class, [Instance::of(Qux::class)]);
        self::assertInstanceOf(Qux::class, $bar->qux);
    }

    public function testDeepInstantiating()
    {
        $a = (new Container())->get(
            A::class,
            [
                'b' => [
                    'class' => B::class,
                    '__construct()' => [
                        'c' => [
                            'class' => C::class,
                            '__construct()' => [
                                'd' => [
                                    'class' => D::class,
                                    '__construct()' => [
                                        'e' => [
                                            'class' => E::class,
                                            '__construct()' => [
                                                'f' => [
                                                    'class' => F::class,
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
        self::assertInstanceOf(A::class, $a);
        self::assertInstanceOf(B::class, $a->b);
        self::assertInstanceOf(C::class, $a->b->c);
        self::assertInstanceOf(D::class, $a->b->c->d);
        self::assertInstanceOf(E::class, $a->b->c->d->e);
        self::assertInstanceOf(F::class, $a->b->c->d->e->f);
    }

    public function testWithSimpleConfiguration()
    {
        $f = (new Container())->get(F::class, [], ['prop' => 1]);
        self::assertInstanceOf(F::class, $f);
        self::assertSame(1, $f->prop);
    }

    public function testWithParamsAndSimpleConfiguration()
    {
        $e = (new Container())->get(E::class, [F::class], ['prop' => 9]);
        self::assertInstanceOf(E::class, $e);
        self::assertInstanceOf(F::class, $e->f);
        self::assertSame(9, $e->prop);
    }
}
