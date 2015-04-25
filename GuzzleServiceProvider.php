<?php

namespace Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\Command\Guzzle\Description;
use GuzzleHttp\Command\Guzzle\GuzzleClient;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Guzzle service provider for Silex
 *
 * = Parameters:
 *  guzzle.services: (optional) array|string|SimpleXMLElement Data describing
 *      your web service clients.  You can pass the path to a file
 *      (.xml|.js|.json), an array of data, or an instantiated SimpleXMLElement
 *      containing configuration data.  See the Guzzle docs for more info.
 *  guzzle.plugins: (optional) An array of guzzle plugins to register with the
 *      client.
 *
 * = Services:
 *   guzzle: An instantiated Guzzle ServiceBuilder.
 *   guzzle.client: A default Guzzle web service client using a dumb base URL.
 *
 * @author Michael Dowling <michael@guzzlephp.org>
 */
class GuzzleServiceProvider implements ServiceProviderInterface
{
    /**
     * Register Guzzle with Silex
     *
     * @param Container $app Application to register with
     */
    public function register(Container $app)
    {
        $app['guzzle.base_url'] = null;
        if(!isset($app['guzzle.plugins'])){
            $app['guzzle.plugins'] = array();
        }

        // Register a Guzzle ServiceBuilder
        $app['guzzle'] = function () use ($app) {
            $builder = new Container();

            if (isset($app['guzzle.services'])) {
                foreach ($app['guzzle.services'] as $name => $service) {
                    $builder[$name.'.description'] = function () use ($service) {
                        return new Description($service);
                    };

                    $builder[$name.'.client'] = function () use ($app) {
                        return new Client();
                    };

                    $builder[$name] = function (Container $builder) use ($name) {
                        return new GuzzleClient($builder[$name.'.client'], $builder[$name.'.description']);
                    };
                }
            }

            return $builder;
        };

        // Register a simple Guzzle Client object (requires absolute URLs when guzzle.base_url is unset)
        $app['guzzle.client'] = function () use ($app) {
            $client = new Client(array_filter(array(
                'base_url' => $app['guzzle.base_url'],
            )));

            $emitter = $client->getEmitter();

            foreach ($app['guzzle.plugins'] as $plugin) {
                $emitter->attach($plugin);
            }

            return $client;
        };
    }
}
