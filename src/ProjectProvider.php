<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ProjectProvider implements ServiceProviderInterface
{
	public function register(Container $app)
	{
		if(!isset($app['env']) && is_string($app['env'])){
			$app['env'] = 'production';
		}

		$app['debug'] = $app['env'] !== 'production';		
		$app['callback_resolver'] = function ($app) { return new Callbacks($app); };

		if(isset($app['config.path'])){
			if(!is_dir($app['config.path'])){
				throw new \Exception('Invalid config path');
			}

			$app['config.path.resolve'] = function($path) use ($app){
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
			};

			$app['config.load'] = function($path) use ($app){
				$file = $app['config.path.resolve']($path);
				$data = json_decode(file_get_contents($file), true);
				$iterator = new \RecursiveArrayIterator($data);

				$keys = [preg_replace('/([^a-zA-Z0-9_\-]+)/', '.', $path)];
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
				return $data;
			};
		}

		$app['registry'] = $app->factory(function(){
			return new Registry();
		});

		$app['trigger'] = $app->protect(function($name, $data = null) use ($app){
			if($app->booted)
            	return $app['dispatcher']->dispatch($name, new Event($data))->getData();
		});
	}
}