<?php

namespace Bangpound\GuzzleHttp\Pimple;

use GuzzleHttp\Event\Emitter;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Guzzle log service provider.
 */
class GuzzleLogServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        $pimple['guzzle.log.template'] = Formatter::CLF;

        $pimple['guzzle.log.formatter'] = function (Container $c) {
            return new Formatter($c['guzzle.log.template']);
        };

        $pimple['guzzle.subscriber.log'] = function (Container $c) {
            return new LogSubscriber($c['logger'], $c['guzzle.log.formatter']);
        };

        $pimple->extend('guzzle.emitter', function (Emitter $emitter, Container $c) {
            $emitter->attach($c['guzzle.subscriber.log']);

            return $emitter;
        });
    }
}
