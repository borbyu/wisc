<?php

namespace Tests\Unit\Container;

use Wisc\ServiceContainer;
use Wisc\ServiceContainerException;

/**
 * Class ServiceContainerTest
 * @package Container
 */
class ServiceContainerTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        ServiceContainer::reset();
    }

    /**
     * @test
     */
    public function testInit()
    {
        $container = ServiceContainer::init();
        $this->assertTrue($container instanceof ServiceContainer);
        $container2 = ServiceContainer::init();
        $this->assertEquals($container, $container2);

    }

    /**
     * @test
     */
    public function testExists()
    {
        $container = ServiceContainer::init();
        $this->assertFalse($container->exists('test'));
        $container->register(
            'test',
            function () {
                return 'test';
            }
        );
        $this->assertTrue($container->exists('test'));
    }

    /**
     * @test
     */
    public function testInitWithDependencyMap()
    {
        $depMap = $this->getMockBuilder('\Wisc\DependencyMapInterface')->getMock();
        $container = ServiceContainer::init($depMap);
        $this->assertTrue($container instanceof ServiceContainer);
    }

    /**
     * @test
     */
    public function testGet()
    {
        ServiceContainer::init();
        $container = ServiceContainer::get();
        $this->assertTrue($container instanceof ServiceContainer);
    }

    /**
     * @test
     */
    public function testGetWithOutInit()
    {
        try {
            $container = ServiceContainer::get();
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof ServiceContainerException);
            return;
        }
        if (isset($container)) {
            $this->fail("Failed to raise expected exception");
        }
    }

    /**
     * @test
     */
    public function testGetWithInit()
    {
        $depMap = $this->getMockBuilder('\Wisc\DependencyMapInterface')->getMock();
        $container = ServiceContainer::get(true, $depMap);
        $this->assertTrue($container instanceof ServiceContainer);
    }

    /**
     * @test
     */
    public function testGetNoInitWithDependencyMap()
    {
        ServiceContainer::init();
        $depMap = $this->getMockBuilder('\Wisc\DependencyMapInterface')->getMock();
        $container = ServiceContainer::get(false, $depMap);
        $this->assertTrue($container instanceof ServiceContainer);
    }

    /**
     * @test
     */
    public function testRegisterAndLocateWithOutCache()
    {
        $container = ServiceContainer::init();
        $container->register(
            'foo',
            function () {
                return microtime();
            },
            false
        );
        $service1 = $container->locate('foo');
        $val1 = $service1;
        usleep(100);
        $service2 = $container->locate('foo');
        $val2 = $service2;
        $this->assertTrue($val1 != $val2);
    }

    /**
     * @test
     */
    public function testRegisterAndLocateWithCache()
    {
        $container = ServiceContainer::init();
        $container->register(
            'foo',
            function () {
                return microtime();
            },
            true
        );
        $service1 = $container->locate('foo');
        $val1 = $service1;
        usleep(100);
        $service2 = $container->locate('foo');
        $val2 = $service2;
        $this->assertTrue($val1 == $val2);
    }

    /**
     * @test
     */
    public function testLocateUnregisteredService()
    {
        $container = ServiceContainer::init();
        try {
            $container->locate('foo');
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof ServiceContainerException);
            return;
        }
        if (isset($container)) {
            $this->fail("Failed to raise expected exception");
        }
    }

    /**
     * @test
     */
    public function testLocateWIthParameter()
    {
        $container = ServiceContainer::init();
        $container->register(
            'foo',
            function ($param) {
                return $param;
            },
            true
        );
        $bar = $container->locate('foo', "bar");
        $this->assertTrue($bar == "bar");
    }

    /**
     * @test
     */
    public function testAppLevel()
    {
        $container = ServiceContainer::init(null, 3);
        $this->assertTrue($container->getAppLevel() == 3);
        $container = ServiceContainer::init(null, 5);
        $this->assertTrue($container->getAppLevel() == 5);
        $container = ServiceContainer::init();
        $this->assertTrue($container->getAppLevel() == 0);
    }

    /**
     * @test
     * @expectedException \Wisc\ServiceContainerException
     */
    public function testInvalidAppLevel()
    {
        ServiceContainer::init(null, 6);
    }
}
