<?php

namespace Drupal\Silex;

use Silex\Application;
use Silex\ServiceProviderInterface;

class DrupalServiceProvider implements ServiceProviderInterface
{
    /**
     * Implements ServiceProviderInterface::register().
     */
    public function register(Application $app)
    {
        if (!isset($app['drupal.client.class'])) {
            $app['drupal.client.class'] = 'Drupal\Silex\DrupalClient';
        }

        if (!isset($app['drupal.hostname'])) {
            $app['drupal.hostname'] = 'http://localhost';
        }

        if (!isset($app['drupal.endpoint'])) {
            $app['drupal.endpoint'] = 'silex';
        }

        if (!isset($app['drupal.base_path'])) {
            $app['drupal.base_path'] = '/';
        }

        $app['drupal.client'] = $app->share(function () use ($app) {
            return new $app['drupal.client.class'](
                $app['session'],
                $app['drupal.hostname'],
                $app['drupal.endpoint'],
                $app['drupal.base_path']
            );
        });

        $app['security.authentication_listener.factory.drupal'] = $app->protect(function ($name, $options) use ($app) {

            $app['security.authentication_provider.' . $name . '.drupal'] = $app->share(function () use ($app, $name) {
                return new DrupalAuthenticationProvider($app['drupal.client'], $app['security.user_checker'], $name);
            });

            return array(
                'security.authentication_provider.' . $name . '.drupal',
                'security.authentication_listener.' . $name . '.form',
                'security.entry_point.' . $name . '.form',
                'form',
            );
        });
    }

    public function boot(Application $app)
    {
    }
}
