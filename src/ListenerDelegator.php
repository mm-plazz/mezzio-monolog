<?php

namespace Plazz\Mezzio\Monolog;

use Laminas\Stratigility\Middleware\ErrorHandler;
use Plazz\Mezzio\Monolog\Listener\Listener;
use Laminas\ServiceManager\Factory\DelegatorFactoryInterface;
use Psr\Container\ContainerInterface;

class ListenerDelegator implements DelegatorFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null): ErrorHandler
    {
        $listener = $container->get(Listener::class);

        /** @var ErrorHandler $errorHandler */
        $errorHandler = $callback();

        if ($listener->isEnabled() === true) {
            $errorHandler->attachListener($listener);
        }

        return $errorHandler;
    }
}
