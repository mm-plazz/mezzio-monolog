<?php

namespace Plazz\Mezzio\Monolog\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use function Sentry\captureEvent;
use Sentry\Event;
use Sentry\EventHint;
use Sentry\Severity;
use Sentry\State\Scope;
use function Sentry\withScope;
use Throwable;

class SentryHandler extends AbstractProcessingHandler
{
    protected function write(LogRecord $record): void
    {
        $event = Event::createEvent();
        $event->setMessage($record->message);
        $event->setLevel(self::getSeverityFromLevel($record->level));

        withScope(function (Scope $scope) use ($record, $event): void {
            $hint = null;
            if (isset($record->context['exception']) && $record->context['exception'] instanceof Throwable) {
                $hint = EventHint::fromArray(['exception' => $record->context['exception']]);
                $context = $record->context;
                unset($context['exception']);
                $record = $record->with(...['context' => $context]);
            }

            $scope->setExtra('monolog.channel', $record->channel);
            $scope->setExtra('monolog.level', $record->level->getName());

            foreach ($record->context as $key => $value) {
                $scope->setExtra((string) $key, $value);
            }

            foreach ($record->extra as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subkey => $subvalue) {
                        $scope->setExtra(
                            sprintf('%s.%s', $key, $subkey),
                            $subvalue
                        );
                    }
                } else {
                    $scope->setExtra((string) $key, $value);
                }
            }

            captureEvent($event, $hint);
        });
    }

    private static function getSeverityFromLevel(Level $level): Severity
    {
        return match ($level) {
            Level::Debug => Severity::debug(),
            Level::Warning => Severity::warning(),
            Level::Error => Severity::error(),
            Level::Critical, Level::Alert, Level::Emergency => Severity::fatal(),
            default => Severity::info(),
        };
    }
}
