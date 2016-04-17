<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ProjectProvider implements ServiceProviderInterface
{
	public function register(Container $app)
	{
		$app['env.name'] = 'APPLICATION_ENV';
		$app['env'] = function() use ($app){
			return getenv($app['env.name']) ? getenv($app['env.name']) : 'production'; 
		};
		$app['debug'] = function() use ($app){
			return $app['env'] !== 'production';
		};

		$app['callback_resolver'] = function ($app) { return new Callbacks($app); };

		$app['config.path.resolve'] = $app->protect(function($path) use ($app){

			if(!isset($app['config.path'])){
				throw new \Exception('Missing config path');
			}

			$base = $app['config.path'].'/'.$path;

			if($app['env'] !== 'production'){
				$path = $base.'.'.$app['env'].'.json';
				if(file_exists($path)){
					return $path;
				}
			}

			$path = $base.'.json';
			if(!file_exists($path)){
				throw new \Exception('Invalid config file: '.$path);
			}

			return $path;
		});

		$app['config.load'] = $app->protect(function($path, $flatten = true) use ($app){
			$file = $app['config.path.resolve']($path);
			$data = json_decode(file_get_contents($file), true);
			$key = preg_replace('/([^a-zA-Z0-9_\-]+)/', '.', $path);
			if($flatten){
				$iterator = new \RecursiveArrayIterator($data);
				$keys = [$key];
				$iterate    = function($iterator) use (&$keys, &$iterate, $app){
	                while( $iterator->valid() ) {
	                    if( $iterator->hasChildren() ) {
	                        array_push($keys, $iterator->key());
	                        $iterate($iterator->getChildren());
	                    }else{
	                        $app[implode('.', $keys).'.'.$iterator->key()] = $iterator->current();
	                    }
	                    $iterator -> next(); 
	                }
	                array_pop($keys);
	            };
	            iterator_apply($iterator, $iterate, array($iterator));	
			}else{
				$app[$key] = $data;
			}
			return $data;
		});
	

		$app['registry'] = $app->factory(function(){
			return new Registry();
		});

		$app['trigger'] = $app->protect(function($name, $data = null) use ($app){
			if($app->booted)
            	return $app['dispatcher']->dispatch($name, new Event($data))->getData();
		});
	}
}