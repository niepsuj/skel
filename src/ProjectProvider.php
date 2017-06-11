<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Skel\Project\Callbacks;
use Skel\Project\Registry;
use Skel\Project\Store;
use Symfony\Component\HttpFoundation\Request;

class ProjectProvider implements ServiceProviderInterface
{
	public function register(Container $app)
	{
	    if(!isset($app['env.name'])){
            $app['env.name'] = 'APPLICATION_ENV';
        }

		if(!isset($app['env'])){
            $app['env'] = function($app){
                return getenv($app['env.name']) ? getenv($app['env.name']) : 'production';
            };
        }

        $app['debug'] = function($app){
            return $app['env'] !== 'production';
        };

		if(!isset($app['root.path'])){
            $app['root.path'] = function(){
                $loader = new \ReflectionClass('Composer\Autoload\ClassLoader');
                return realpath(dirname($loader->getFileName()) . '/../../');
            };
        }

        if(!isset($app['tmp.path'])){
            $app['tmp.path'] = function($app){
                return $app['root.path'] . '/tmp';
            };
        }

        if(!isset($app['src.path'])){
            $app['src.path'] = function($app){
                return $app['root.path'] . '/src';
            };
        }

        if(!isset($app['vendor.path'])){
            $app['vendor.path'] = function($app){
                return $app['root.path'] . '/vendor';
            };
        }

        if(!isset($app['log.path'])){
            $app['log.path'] = function($app){
                return $app['root.path'] . '/log';
            };
        }

        if(!isset($app['public.path'])){
            $app['public.path'] = function($app){
                return $app['root.path'] . '/public';
            };
        }

		$app['composer'] = function($app){
		    return json_decode(
                file_get_contents(
                    $app['root.path'].'/composer.json'
                ), true
            );
        };

		$app['name'] = function($app){
			return
                preg_replace(
                    '/\.+/',
                    '.',
                    preg_replace(
                        '/[^a-z0-9_\.]+/',
                        '.',
                        strtolower(
                            $app['composer']['name']
                        )
                    )
                );
		};

		$app['version'] = function($app){
		    $version =
                isset($app['composer']['version']) ?
                    $app['composer']['version'] : '0';

		    if(file_exists($app['root.path'].'/.git/HEAD')){
                $head = str_replace(
                    'ref: ',
                    '',
                    file_get_contents(
                        $app['root.path'].'/.git/HEAD'
                    )
                );

                $headFile = $app['root.path'].'.git/'.$head;
                $version .= '.'.file_get_contents( $headFile );
            }
            return $version;
        };

		$app['registry'] = $app->factory(function(){
			return new Registry();
		});

		$app['store'] = $app->factory(function(){
		    return new Store();
        });

		$app['cleanup.scope'] = function(){
		    return new \ArrayObject();
        };

		$app->before(function (Request $request) {
			if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
				$data = json_decode($request->getContent(), true);
				$request->request->replace(is_array($data) ? $data : array());
			}
		});
	}
}