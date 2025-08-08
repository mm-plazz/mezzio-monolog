# Mezzio Monolog ErrorHandler

[![Latest Stable Version](https://poser.pugx.org/mm-plazz/mezzio-monolog/v/stable)](https://packagist.org/packages/mm-plazz/mezzio-monolog)
[![Total Downloads](https://poser.pugx.org/Plazz/mezzio-monolog/downloads)](https://packagist.org/packages/mm-plazz/mezzio-monolog)
[![Monthly Downloads](https://poser.pugx.org/Plazz/mezzio-monolog/d/monthly.png)](https://packagist.org/packages/mm-plazz/mezzio-monolog)
[![Software License](https://img.shields.io/badge/license-GPL--3.0-brightgreen.svg)](LICENSE)
[![Build Status](https://github.com/mm-plazz/mezzio-monolog/actions/workflows/php.yml/badge.svg)](https://github.com/mm-plazz/mezzio-monolog/actions/workflows/php.yml)

This library enables [Monolog](https://github.com/Seldaek/monolog) as an ErrorHandler in [Mezzio](https://getmezzio.org/).

It catches all uncaught exceptions and logs them to the configured Monolog handlers.

## What is Mezzio?

Mezzio is a PHP framework for building web applications and APIs. It is based on a middleware pipeline architecture, which makes it flexible and extensible.

## What is Monolog?

Monolog is a popular logging library for PHP. It supports a wide variety of handlers, which allows you to send your logs to files, sockets, inboxes, databases and various web services.

## Install

Install the library using composer:

```bash
composer require mm-plazz/mezzio-monolog
```

## Configuration

First, you need to enable the component by adding the `Plazz\Mezzio\Monolog\ConfigProvider` to your `config/config.php`:

```php
<?php

use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ConfigAggregator\ArrayProvider;
use Laminas\ConfigAggregator\PhpFileProvider;

$cacheConfig = [
    'config_cache_path' => 'data/cache/config-cache.php',
];

$aggregator = new ConfigAggregator([
    \Plazz\Mezzio\Monolog\ConfigProvider::class, // <-- Add this line

    // ... other providers

], $cacheConfig['config_cache_path']);

return $aggregator->getMergedConfig();
```

Next, create a `config/autoload/monolog.global.php` file to configure the Monolog handlers:

```php
<?php

declare(strict_types=1);

use Monolog\Level;

return [
    'monolog' => [
        // StreamHandler configuration
        'stream' => [
            'path' => 'data/log/myapp.log',
            'level' => Level::Debug,
        ],
        // SentryHandler configuration
        'sentry' => [
            'dsn' => 'https://xxxxx@sentry.io/12345',
            'level' => Level::Debug,
        ],
    ],
];
```

The Monolog ErrorHandler will be active only when Mezzio is in "production mode" (`$config['debug']` is `false`).
To switch to "production mode", you can use `composer run development-disable`.
To switch back to "development mode", use `composer run development-enable`.

### Handlers

This library supports the following Monolog handlers out of the box:

- **`StreamHandler`**: Logs records into any PHP stream, which is ideal for log files.
    - `path` (string): The path to the log file. Defaults to `data/log/app.log`.
    - `level` (Monolog\Level): The minimum logging level at which this handler will be triggered. Defaults to `Level::Debug`.

- **`SentryHandler`**: Logs records to [Sentry.io](https://sentry.io/). This handler requires the `sentry/sdk` package.
    - `dsn` (string): Your Sentry DSN.
    - `level` (Monolog\Level): The minimum logging level at which this handler will be triggered. Defaults to `Level::Debug`.
    - Any other options will be passed to the Sentry `init()` function.

## Usage

This library automatically registers the Monolog listener with Mezzio's `ErrorHandler`. No further configuration is needed to make it work.

If you want to use the Monolog logger in your own classes, you can fetch the `Plazz\Mezzio\Monolog\Listener\Listener` service from the container. This class provides all the logging methods from the PSR-3 logger interface.

Here is an example of how to use it in a factory:

```php
<?php

namespace App\Factory;

use App\MyService;
use Psr\Container\ContainerInterface;
use Plazz\Mezzio\Monolog\Listener\Listener as MonologListener;

class MyServiceFactory
{
    public function __invoke(ContainerInterface $container): MyService
    {
        $monologListener = $container->get(MonologListener::class);
        return new MyService($monologListener);
    }
}
```

And in your service:

```php
<?php

namespace App;

use Plazz\Mezzio\Monolog\Listener\Listener as MonologListener;

class MyService
{
    private MonologListener $logger;

    public function __construct(MonologListener $logger)
    {
        $this->logger = $logger;
    }

    public function doSomething(): void
    {
        $this->logger->info('Doing something...');
        // ...
    }
}
```

## Contributing

Contributions are welcome! Please feel free to submit a pull request or open an issue.

## License

This project is licensed under the GPL-3.0 License - see the [LICENSE](LICENSE) file for details.
