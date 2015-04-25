<?php

namespace Bangpound\GuzzleHttp\Pimple;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use GuzzleHttp\Event\Emitter;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Guzzle service provider for Silex.
 *
 * = Parameters:
 *  guzzle.client.defaults: (optional) array Default request options to apply to each request.
 *  guzzle.services: (optional) array Data describing your web service clients.
 *      See the Guzzle docs for more info.
 *
 * = Services:
 *   guzzle: An instantiated Pimple container for all configured services.
 *   guzzle.client: A default Guzzle web service client using a dumb base URL.
 *
 * @author Michael Dowling <michael@guzzlephp.org>
 */
class GuzzleServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Guzzle with Silex.
     *
     * @param Container $pimple Application to register with
     */
    public function register(Container $pimple)
    {
        $pimple['guzzle.base_url'] = null;

        // Register a Guzzle service container
        $pimple['guzzle'] = function (Container $c) {
            $builder = new Container();

            if (isset($c['guzzle.services'])) {
                foreach ($c['guzzle.services'] as $name => $service) {
                    $builder[$name.'.description'] = function () use ($service) {
                        return new Description($service);
                    };

                    $builder[$name.'.client'] = function () {
                        return new Client();
                    };

                    $builder[$name] = function (Container $builder) use ($name) {
                        return new GuzzleClient($builder[$name.'.client'], $builder[$name.'.description']);
                    };
                }
            }

            return $builder;
        };

        // Default request options to apply to each request
        $pimple['guzzle.client.defaults'] = array();

        // Guzzle event emitter. Extend this to attach event subscribers.
        $pimple['guzzle.emitter'] = function () {
            return new Emitter();
        };

        // Register a simple Guzzle Client object (requires absolute URLs when guzzle.base_url is unset)
        $pimple['guzzle.client'] = function (Container $c) {
            $client = new Client(array_filter(array(
                'base_url' => $c['guzzle.base_url'],
                'defaults' => $c['guzzle.client.defaults'],
                'emitter' => $c['guzzle.emitter'],
            )));

            return $client;
        };
    }
}
