<?php

namespace Plazz\Mezzio\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use function Sentry\captureEvent;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Severity;
use Sentry\State\Scope;
use function Sentry\withScope;
use Throwable;

class SentryHandler extends AbstractProcessingHandler
{
    protected function write(array $record): void
    {
        $event = Event::createEvent();
        $event->setMessage($record['message']);
        $event->setLevel(self::getSeverityFromLevel($record['level']));

        withScope(function (Scope $scope) use ($record, $event): void {
            // $scope->clear();

            if (isset($record['context']['exception']) && $record['context']['exception'] instanceof Throwable) {
                $hint = EventHint::fromArray(['exception' => $record['context']['exception']]);

                unset($record['context']['exception']);
            }

            $scope->setExtra('monolog.channel', $record['channel']);
            $scope->setExtra('monolog.level', $record['level_name']);

            $context = $record['context'] ?? [];
            foreach ($context as $key => $value) {
                $scope->setExtra((string) $key, $value);
            }

            $extra = $record['extra'] ?? [];

            foreach ($extra as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subkey => $subvalue) {
                        $scope->setExtra(
                            sprintf('%s.%s', (string) $key, (string) $subkey),
                            $subvalue
                        );
                    }
                } else {
                    $scope->setExtra((string) $key, $value);
                }
            }

            captureEvent($event, $hint ?? null);
        });
    }

    private static function getSeverityFromLevel(int $level): Severity
    {
        return match ($level) {
            Logger::DEBUG => Severity::debug(),
            Logger::WARNING => Severity::warning(),
            Logger::ERROR => Severity::error(),
            Logger::CRITICAL, Logger::ALERT, Logger::EMERGENCY => Severity::fatal(),
            default => Severity::info(),
        };
    }
}
