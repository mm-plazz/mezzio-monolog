<?php

namespace Plazz\Mezzio\Monolog\Listener;

use Psr\Container\ContainerInterface;

class ListenerFactory
{
    /**
     * @param ContainerInterface $container
     * @return Listener
     */
    public function __invoke(ContainerInterface $container): Listener
    {
        /** @var array<string, mixed> $config */
        $config = $container->get('config');

        /** @var array<string, mixed> $monolog */
        $monolog = $config['monolog'] ?? [];
        $debug = (bool) ($config['debug'] ?? false);

        return new Listener($monolog, $debug);
    }
}
