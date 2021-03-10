<?php

namespace Bizley\Tests;

use Bizley\DeepInstantiate\Container;
use Bizley\Tests\YiiModels\Alpha;
use Bizley\Tests\YiiModels\Bar;
use Bizley\Tests\YiiModels\BarSetter;
use Bizley\Tests\YiiModels\Beta;
use Bizley\Tests\YiiModels\Car;
use Bizley\Tests\YiiModels\Cat;
use Bizley\Tests\YiiModels\Corge;
use Bizley\Tests\YiiModels\Foo;
use Bizley\Tests\YiiModels\FooProperty;
use Bizley\Tests\YiiModels\Kappa;
use Bizley\Tests\YiiModels\Order;
use Bizley\Tests\YiiModels\Qux;
use Bizley\Tests\YiiModels\QuxFactory;
use Bizley\Tests\YiiModels\QuxInterface;
use Bizley\Tests\YiiModels\Type;
use Bizley\Tests\YiiModels\Variadic;
use Bizley\Tests\YiiModels\Zeta;
use Yii;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\validators\NumberValidator;

/**
 * These are tests copied from https://github.com/yiisoft/yii2/blob/master/tests/framework/di/ContainerTest.php
 * and slightly refactored. All to make sure extended version works like the original one.
 */
class YiiContainerTest extends YiiTestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        Yii::$container = new Container();
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function testDefault()
    {
        $Foo = Foo::class;
        $Bar = Bar::class;
        $Qux = Qux::class;

        // automatic wiring
        $container = new Container();
        $container->set(QuxInterface::class, $Qux);
        $foo = $container->get($Foo);
        self::assertInstanceOf($Foo, $foo);
        self::assertInstanceOf($Bar, $foo->bar);
        self::assertInstanceOf($Qux, $foo->bar->qux);
        $foo2 = $container->get($Foo);
        self::assertNotSame($foo, $foo2);

        // full wiring
        $container = new Container();
        $container->set(QuxInterface::class, $Qux);
        $container->set($Bar);
        $container->set($Qux);
        $container->set($Foo);
        $foo = $container->get($Foo);
        self::assertInstanceOf($Foo, $foo);
        self::assertInstanceOf($Bar, $foo->bar);
        self::assertInstanceOf($Qux, $foo->bar->qux);

        // wiring by closure
        $container = new Container();
        $container->set('foo', function () {
            $qux = new Qux();
            $bar = new Bar($qux);
            return new Foo($bar);
        });
        $foo = $container->get('foo');
        self::assertInstanceOf($Foo, $foo);
        self::assertInstanceOf($Bar, $foo->bar);
        self::assertInstanceOf($Qux, $foo->bar->qux);

        // wiring by closure which uses container
        $container = new Container();
        $container->set(QuxInterface::class, $Qux);
        $container->set('foo', function (Container $c, $params, $config) {
            return $c->get(Foo::class);
        });
        $foo = $container->get('foo');
        self::assertInstanceOf($Foo, $foo);
        self::assertInstanceOf($Bar, $foo->bar);
        self::assertInstanceOf($Qux, $foo->bar->qux);

        // predefined constructor parameters
        $container = new Container();
        $container->set('foo', $Foo, [Instance::of('bar')]);
        $container->set('bar', $Bar, [Instance::of('qux')]);
        $container->set('qux', $Qux);
        $foo = $container->get('foo');
        self::assertInstanceOf($Foo, $foo);
        self::assertInstanceOf($Bar, $foo->bar);
        self::assertInstanceOf($Qux, $foo->bar->qux);

        // predefined property parameters
        $fooSetter = FooProperty::class;
        $barSetter = BarSetter::class;

        $container = new Container();
        $container->set('foo', ['class' => $fooSetter, 'bar' => Instance::of('bar')]);
        $container->set('bar', ['class' => $barSetter, 'qux' => Instance::of('qux')]);
        $container->set('qux', $Qux);
        $foo = $container->get('foo');
        self::assertInstanceOf($fooSetter, $foo);
        self::assertInstanceOf($barSetter, $foo->bar);
        self::assertInstanceOf($Qux, $foo->bar->qux);

        // wiring by closure
        $container = new Container();
        $container->set('qux', new Qux());
        $qux1 = $container->get('qux');
        $qux2 = $container->get('qux');
        self::assertSame($qux1, $qux2);

        // config
        $container = new Container();
        $container->set('qux', $Qux);
        $qux = $container->get('qux', [], ['a' => 2]);
        self::assertEquals(2, $qux->a);
        $qux = $container->get('qux', [3]);
        self::assertEquals(3, $qux->a);
        $qux = $container->get('qux', [3, ['a' => 4]]);
        self::assertEquals(4, $qux->a);
    }

    public function testInvoke()
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    'class' => 'Bizley\Tests\YiiModels\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    'class' => 'Bizley\Tests\YiiModels\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        Yii::$container->set('Bizley\Tests\YiiModels\QuxInterface', [
            'class' => 'Bizley\Tests\YiiModels\Qux',
            'a' => 'independent',
        ]);

        // use component of application
        $callback = function ($param, QuxInterface $qux, Bar $bar) {
            return [$param, $qux instanceof Qux, $qux->a, $bar->qux->a];
        };
        $result = Yii::$container->invoke($callback, ['D426']);
        self::assertEquals(['D426', true, 'belongApp', 'independent'], $result);

        // another component of application
        $callback = function ($param, QuxInterface $qux2, $other = 'default') {
            return [$param, $qux2 instanceof Qux, $qux2->a, $other];
        };
        $result = Yii::$container->invoke($callback, ['M2792684']);
        self::assertEquals(['M2792684', true, 'belongAppQux2', 'default'], $result);

        // component not belong application
        $callback = function ($param, QuxInterface $notBelongApp, $other) {
            return [$param, $notBelongApp instanceof Qux, $notBelongApp->a, $other];
        };
        $result = Yii::$container->invoke($callback, ['MDM', 'not_default']);
        self::assertEquals(['MDM', true, 'independent', 'not_default'], $result);

        $myFunc = function ($a, NumberValidator $b, $c = 'default') {
            return [$a, \get_class($b), $c];
        };
        $result = Yii::$container->invoke($myFunc, ['a']);
        self::assertEquals(['a', 'yii\validators\NumberValidator', 'default'], $result);

        $result = Yii::$container->invoke($myFunc, ['ok', 'value_of_c']);
        self::assertEquals(['ok', 'yii\validators\NumberValidator', 'value_of_c'], $result);

        // use native php function
        self::assertEquals(Yii::$container->invoke('trim', [' M2792684  ']), 'M2792684');

        // use helper function
        $array = ['M36', 'D426', 'Y2684'];
        self::assertFalse(Yii::$container->invoke(['yii\helpers\ArrayHelper', 'isAssociative'], [$array]));


        $myFunc = function (\yii\console\Request $request, \yii\console\Response $response) {
            return [$request, $response];
        };
        list($request, $response) = Yii::$container->invoke($myFunc);
        self::assertEquals($request, Yii::$app->request);
        self::assertEquals($response, Yii::$app->response);
    }

    public function testAssociativeInvoke()
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    'class' => 'Bizley\Tests\YiiModels\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    'class' => 'Bizley\Tests\YiiModels\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        $closure = function ($a, $x = 5, $b) {
            return $a > $b;
        };
        self::assertFalse(Yii::$container->invoke($closure, ['b' => 5, 'a' => 1]));
        self::assertTrue(Yii::$container->invoke($closure, ['b' => 1, 'a' => 5]));
    }

    public function testResolveCallableDependencies()
    {
        $this->mockApplication([
            'components' => [
                'qux' => [
                    'class' => 'Bizley\Tests\YiiModels\Qux',
                    'a' => 'belongApp',
                ],
                'qux2' => [
                    'class' => 'Bizley\Tests\YiiModels\Qux',
                    'a' => 'belongAppQux2',
                ],
            ],
        ]);
        $closure = function ($a, $b) {
            return $a > $b;
        };
        self::assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, ['b' => 5, 'a' => 1]));
        self::assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, ['a' => 1, 'b' => 5]));
        self::assertEquals([1, 5], Yii::$container->resolveCallableDependencies($closure, [1, 5]));
    }

    public function testOptionalDependencies()
    {
        $container = new Container();
        // Test optional unresolvable dependency.
        $closure = function (QuxInterface $test = null) {
            return $test;
        };
        self::assertNull($container->invoke($closure));
    }

    public function testSetDependencies()
    {
        $container = new Container();
        $container->setDefinitions([
            'model.order' => Order::class,
            Cat::class => Type::class,
            'test\TraversableInterface' => [
                ['class' => 'Bizley\Tests\YiiModels\TraversableObject'],
                [['item1', 'item2']],
            ],
            'qux.using.closure' => function () {
                return new Qux();
            },
            'rollbar',
            'baibaratsky\yii\rollbar\Rollbar'
        ]);
        $container->setDefinitions([]);

        self::assertInstanceOf(Order::class, $container->get('model.order'));
        self::assertInstanceOf(Type::class, $container->get(Cat::class));

        $traversable = $container->get('test\TraversableInterface');
        self::assertInstanceOf('Bizley\Tests\YiiModels\TraversableObject', $traversable);
        self::assertEquals('item1', $traversable->current());

        self::assertInstanceOf('Bizley\Tests\YiiModels\Qux', $container->get('qux.using.closure'));

        try {
            $container->get('rollbar');
            self::fail('InvalidConfigException was not thrown');
        } catch (\Exception $e) {
            self::assertInstanceOf(InvalidConfigException::class, $e);
        }
    }

    public function testStaticCall()
    {
        $container = new Container();
        $container->setDefinitions([
            'qux' => [QuxFactory::class, 'create'],
        ]);

        $qux = $container->get('qux');
        self::assertInstanceOf(Qux::class, $qux);
        self::assertSame(42, $qux->a);
    }

    public function testObject()
    {
        $container = new Container();
        $container->setDefinitions([
            'qux' => new Qux(42),
        ]);

        $qux = $container->get('qux');
        self::assertInstanceOf(Qux::class, $qux);
        self::assertSame(42, $qux->a);
    }

    public function testDi3Compatibility()
    {
        $container = new Container();
        $container->setDefinitions([
            'test\TraversableInterface' => [
                '__class' => 'Bizley\Tests\YiiModels\TraversableObject',
                '__construct()' => [['item1', 'item2']],
            ],
            'qux' => [
                '__class' => Qux::class,
                'a' => 42,
            ],
        ]);

        $qux = $container->get('qux');
        self::assertInstanceOf(Qux::class, $qux);
        self::assertSame(42, $qux->a);

        $traversable = $container->get('test\TraversableInterface');
        self::assertInstanceOf('Bizley\Tests\YiiModels\TraversableObject', $traversable);
        self::assertEquals('item1', $traversable->current());
    }

    public function testInstanceOf()
    {
        $container = new Container();
        $container->setDefinitions([
            'qux' => [
                'class' => Qux::class,
                'a' => 42,
            ],
            'bar' => [
                '__class' => Bar::class,
                '__construct()' => [
                    Instance::of('qux')
                ],
            ],
        ]);
        $bar = $container->get('bar');
        self::assertInstanceOf(Bar::class, $bar);
        $qux = $bar->qux;
        self::assertInstanceOf(Qux::class, $qux);
        self::assertSame(42, $qux->a);
    }

    public function testReferencesInArrayInDependencies()
    {
        $quxInterface = 'Bizley\Tests\YiiModels\QuxInterface';
        $container = new Container();
        $container->resolveArrays = true;
        $container->setSingletons([
            $quxInterface => [
                'class' => Qux::class,
                'a' => 42,
            ],
            'qux' => Instance::of($quxInterface),
            'bar' => [
                'class' => Bar::class,
            ],
            'corge' => [
                '__class' => Corge::class,
                '__construct()' => [
                    [
                        'qux' => Instance::of('qux'),
                        'bar' => Instance::of('bar'),
                        'q33' => new Qux(33),
                    ],
                ],
            ],
        ]);
        $corge = $container->get('corge');
        self::assertInstanceOf(Corge::class, $corge);
        $qux = $corge->map['qux'];
        self::assertInstanceOf(Qux::class, $qux);
        self::assertSame(42, $qux->a);
        $bar = $corge->map['bar'];
        self::assertInstanceOf(Bar::class, $bar);
        self::assertSame($qux, $bar->qux);
        $q33 = $corge->map['q33'];
        self::assertInstanceOf(Qux::class, $q33);
        self::assertSame(33, $q33->a);
    }

    public function testGetByInstance()
    {
        $container = new Container();
        $container->setSingletons([
            'one' => Qux::class,
            'two' => Instance::of('one'),
        ]);
        $one = $container->get(Instance::of('one'));
        $two = $container->get(Instance::of('two'));
        self::assertInstanceOf(Qux::class, $one);
        self::assertSame($one, $two);
        self::assertSame($one, $container->get('one'));
        self::assertSame($one, $container->get('two'));
    }

    public function testWithoutDefinition()
    {
        $container = new Container();

        $one = $container->get(Qux::class);
        $two = $container->get(Qux::class);
        self::assertInstanceOf(Qux::class, $one);
        self::assertInstanceOf(Qux::class, $two);
        self::assertSame(1, $one->a);
        self::assertSame(1, $two->a);
        self::assertNotSame($one, $two);
    }

    public function testGetByClassIndirectly()
    {
        $container = new Container();
        $container->setSingletons([
            'qux' => Qux::class,
            Qux::class => [
                'a' => 42,
            ],
        ]);

        $qux = $container->get('qux');
        self::assertInstanceOf(Qux::class, $qux);
        self::assertSame(42, $qux->a);
    }

    public function testThrowingNotFoundException()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $container = new Container();
        $container->get('non_existing');
    }

    public function testContainerSingletons()
    {
        $container = new Container();
        $container->setSingletons([
            'model.order' => Order::class,
            'test\TraversableInterface' => [
                ['class' => 'Bizley\Tests\YiiModels\TraversableObject'],
                [['item1', 'item2']],
            ],
            'qux.using.closure' => function () {
                return new Qux();
            },
        ]);
        $container->setSingletons([]);

        $order = $container->get('model.order');
        $sameOrder = $container->get('model.order');
        self::assertSame($order, $sameOrder);

        $traversable = $container->get('test\TraversableInterface');
        $sameTraversable = $container->get('test\TraversableInterface');
        self::assertSame($traversable, $sameTraversable);

        $foo = $container->get('qux.using.closure');
        $sameFoo = $container->get('qux.using.closure');
        self::assertSame($foo, $sameFoo);
    }

    public function testVariadicConstructor()
    {
        $container = new Container();
        self::assertInstanceOf(Variadic::class, $container->get('Bizley\Tests\YiiModels\Variadic'));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testVariadicCallable()
    {
        require __DIR__ . '/testContainerWithVariadicCallable.php';
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testDelayedInitializationOfSubArray()
    {
        $definitions = [
            'test' => [
                'class' => Corge::class,
                '__construct()' => [
                    [Instance::of('setLater')],
                ],
            ],
        ];

        $application = Yii::createObject([
            '__class' => \yii\web\Application::class,
            'basePath' => __DIR__,
            'id' => 'test',
            'components' => [
                'request' => [
                    'baseUrl' => '123'
                ],
            ],
            'container' => [
                'definitions' => $definitions,
            ],
        ]);

        Yii::$container->set('setLater', new Qux());
        Yii::$container->get('test');
    }

    public function testNulledConstructorParameters()
    {
        $alpha = (new Container())->get(Alpha::class);
        self::assertInstanceOf(Beta::class, $alpha->beta);
        self::assertNull($alpha->omega);

        $QuxInterface = 'Bizley\Tests\YiiModels\QuxInterface';
        $container = new Container();
        $container->set($QuxInterface, Qux::class);
        $alpha = $container->get(Alpha::class);
        self::assertInstanceOf(Beta::class, $alpha->beta);
        self::assertInstanceOf($QuxInterface, $alpha->omega);
        self::assertNull($alpha->unknown);
        self::assertNull($alpha->color);

        $container = new Container();
        $container->set('Bizley\Tests\YiiModels\AbstractColor', 'Bizley\Tests\YiiModels\Color');
        $alpha = $container->get(Alpha::class);
        self::assertInstanceOf('Bizley\Tests\YiiModels\Color', $alpha->color);
    }

    public function testNamedConstructorParameters()
    {
        $test = (new Container())->get(Car::class, [
            'name' => 'Hello',
            'color' => 'red',
        ]);
        self::assertSame('Hello', $test->name);
        self::assertSame('red', $test->color);
    }

    public function testInvalidConstructorParameters()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('Dependencies indexed by name and by position in the same array are not allowed.');
        (new Container())->get(Car::class, [
            'color' => 'red',
            'Hello',
        ]);
    }

    public function dataNotInstantiableException()
    {
        return [
            [Bar::class],
            [Kappa::class],
        ];
    }

    /**
     * @dataProvider dataNotInstantiableException
     * @param string $class
     * @throws InvalidConfigException
     */
    public function testNotInstantiableException($class)
    {
        $this->expectException('yii\di\NotInstantiableException');
        (new Container())->get($class);
    }

    public function testNullTypeConstructorParameters()
    {
        if (PHP_VERSION_ID < 70100) {
            self::markTestSkipped('Can not be tested on PHP < 7.1');
        }

        $zeta = (new Container())->get(Zeta::class);
        self::assertInstanceOf(Beta::class, $zeta->beta);
        self::assertInstanceOf(Beta::class, $zeta->betaNull);
        self::assertNull($zeta->color);
        self::assertNull($zeta->colorNull);
        self::assertNull($zeta->qux);
        self::assertNull($zeta->quxNull);
        self::assertNull($zeta->unknown);
        self::assertNull($zeta->unknownNull);
    }
}
