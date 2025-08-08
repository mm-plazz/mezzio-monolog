<?php

namespace Plazz\Mezzio\Monolog;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Plazz\Mezzio\Monolog\Listener\Listener;
use Psr\Container\ContainerInterface;

class ListenerDelegator
{
    /**
     * @param ContainerInterface $container
     * @param callable $callback
     * @return ErrorHandler
     */
    public function __invoke(
        ContainerInterface $container,
        callable $callback
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
