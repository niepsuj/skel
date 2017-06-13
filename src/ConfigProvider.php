<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Application;
use Skel\Config\Cache;
use Skel\Event\CleanupEvent;
use Skel\ProjectEvents;
use Symfony\Component\HttpKernel\KernelEvents;

class ConfigProvider implements ServiceProviderInterface
{
    public function register(Container $pimple)
    {
        if(!isset($pimple['env'])){
            throw new \Exception('Missing $container[env] key');
        }

        $pimple['config.path'] = function($app){
            return $app['root.path'] . '/config';
        };

        $pimple['config.cache.path'] = function($app){

            if(isset($app['tmp.path'])){
                $dir = $app['tmp.path'].'/config';
            }else{
                $dir = $app['config.path'].'/tmp';
            }

            if(!file_exists($dir)){
                mkdir($dir);
            }

            return $dir;
        };

        $pimple['config.cache.filename'] = function($app){
            if(isset($app['version'])){
                return $app['version'].'.php';
            }

            return 'config.php';
        };

        $pimple['config.cache'] = function($app){
            return new Cache(
                $app['config.cache.path'],
                $app['config.cache.filename']
            );
        };

        $pimple['config'] = function($app){
            return new Config\Service(
                $app['config.path'],
                $app['env'],
                $app['config.cache']
            );
        };

        /** @var Application $pimple */
        $pimple->on(KernelEvents::TERMINATE, function() use ($pimple){
            if($pimple['config.cache'] instanceof Cache){
                $pimple['config.cache']->save();
            }
        });

        if(isset($pimple['cleanup.scope'])){
            $pimple['cleanup.scope'][] = 'config';

            $pimple->on(ProjectEvents::CLEANUP, function(CleanupEvent $cleanupEvent) use ($pimple){
                if($cleanupEvent->can('config')){
                    $cleanupEvent->report(
                        'config',
                        $pimple['config.cache']->cleanup()
                    );
                }
            });
        }
    }
}