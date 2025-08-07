<?php

namespace Plazz\Mezzio\Monolog;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Plazz\Mezzio\Monolog\Listener\Listener;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;

class ListenerDelegator implements DelegatorFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param array|null $options
     * @return ErrorHandler
     */
    public function __invoke(
        ContainerInterface $container,
        $name,
        callable $callback,
        $options = null
    ): ErrorHandler {
        /** @var Listener $listener */
        $listener = $container->get(Listener::class);

        /** @var ErrorHandler $errorHandler */
        $errorHandler = $callback();

        if ($listener->isEnabled() === true) {
            $errorHandler->attachListener($listener);
        }

        return $errorHandler;
    }
}
