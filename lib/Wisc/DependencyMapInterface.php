<?php

namespace Wisc;

/**
 * Interface DependencyMapInterface
 * @package Container
 */
interface DependencyMapInterface
{
    /**
     * @param ServiceContainer $container
     * @return ServiceContainer
     */
    public function init(ServiceContainer $container);

    /**
     * @return DependencyMapInterface
     */
    public static function get();
}
