<?php

namespace Wisc;

/**
 * Class ServiceContainer
 * @package Container
 */
class ServiceContainer
{
    /**
     * @var ServiceContainer
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $registeredServices = [];

    /**
     * @var array
     */
    protected $cachedServices = [];

    /**
     * @var int
     */
    protected static $appLevel = 0;

    /**
     * @return int
     */
    public function getAppLevel()
    {
        return self::$appLevel;
    }

    /**
     * @param DependencyMapInterface $dependencyMap
     * @param int $appLevel
     * @return ServiceContainer
     * @throws ServiceContainerException
     */
    public static function init(DependencyMapInterface $dependencyMap = null, $appLevel = 0)
    {
        if ((int) $appLevel < 0 || (int) $appLevel > 5) {
            throw new ServiceContainerException('Invalid App Level');
        }
        if (!self::$instance) {
            $container = new ServiceContainer();
        } else {
            $container = self::$instance;
        }
        if (!is_null($dependencyMap)) {
            $container->loadDependencyMap($dependencyMap);
        }
        self::$instance = $container;
        self::$appLevel = $appLevel;
        return $container;
    }

    /**
     * Un-initializes the container
     */
    public static function reset()
    {
        self::$instance = null;
    }

    /**
     * @param bool $initialize
     * @param DependencyMapInterface $dependencyMap
     * @return ServiceContainer
     * @throws ServiceContainerException
     */
    public static function get($initialize = false, DependencyMapInterface $dependencyMap = null)
    {
        if (self::$instance instanceof ServiceContainer) {
            if ($dependencyMap) {
                self::$instance->loadDependencyMap($dependencyMap);
            }
            return self::$instance;
        } elseif ($initialize) {
            return self::init($dependencyMap);
        } else {
            throw new ServiceContainerException('Service Container Has Not Been Initialized');
        }
    }

    /**
     * @param string $serviceKey
     * @param mixed $parameter
     * @return mixed
     * @throws ServiceContainerException
     */
    public function locate($serviceKey, $parameter = null)
    {
        if (isset($this->registeredServices[$serviceKey])) {
            if ($this->registeredServices[$serviceKey]["cache"] && isset($this->cachedServices[$serviceKey])) {
                return $this->cachedServices[$serviceKey];
            }
            if (isset($parameter)) {
                $service = $this->registeredServices[$serviceKey]["call"]($parameter);
            } else {
                $service = $this->registeredServices[$serviceKey]["call"]();
            }
            if ($this->registeredServices[$serviceKey]["cache"]) {
                $this->cachedServices[$serviceKey] = $service;
            }
            return $service;
        } else {
            throw new ServiceContainerException('Unable to locate service (' . $serviceKey . ')');
        }
    }

    /**
     * @param string $serviceKey
     * @return bool
     */
    public function exists($serviceKey)
    {
        return isset($this->registeredServices[$serviceKey]);
    }

    /**
     * @param string $key
     * @param \Closure $serviceBuilder
     * @param bool $cache
     */
    public function register($key, \Closure $serviceBuilder, $cache = true)
    {
        $this->registeredServices[$key] = ["call" => $serviceBuilder, "cache" => $cache];
    }

    /**
     * @param DependencyMapInterface $dependencyMap
     */
    public function loadDependencyMap(DependencyMapInterface $dependencyMap)
    {
        $dependencyMap->init($this);
    }
}
