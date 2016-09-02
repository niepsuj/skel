<?php

namespace Skel;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

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
		$app['name'] = function(){
			$loader = new \ReflectionClass('Composer\Autoload\ClassLoader');
			$composer = json_decode(file_get_contents(dirname($loader->getFileName()).'/../../composer.json'), true);
			return $composer['name'];
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

		$app['config.load'] = $app->protect(function($path, $flatten = false) use ($app){
			$file   = $app['config.path.resolve']($path);
			$key    = preg_replace('/([^a-zA-Z0-9_\-]+)/', '.', $path);
			$raw    = file_get_contents($file);
			$data   = json_decode($raw, true);

			if("[" == $raw{0}){
				$app[$key] = $app['registry']($data);
				$app[$key]->setSaveCallback(function($registry) use ($app, $file){
					file_put_contents(
						$file,
						json_encode($registry->flush(), JSON_PRETTY_PRINT)
					);
				});
			}else{
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
							$iterator->next();
						}
						array_pop($keys);
					};
					iterator_apply($iterator, $iterate, array($iterator));
				}else{
					$app[$key] = $app['config']($data);
					$app[$key]->setSaveCallback(function($config) use ($app, $file){
						file_put_contents(
							$file,
							json_encode($config->flush(), JSON_PRETTY_PRINT)
						);
					});
				}
			}

			return $data;
		});
	

		$app['registry'] = $app->protect(function($data = null){
			return new Registry($data);
		});

		$app['config'] = $app->protect(function($data = null){
			return new ConfigRoot($data);
		});

		$app['trigger'] = $app->protect(function($name, $data = null) use ($app){
			return $app['dispatcher']->dispatch($name, new Event($data))->flush();
		});

		$app->before(function (Request $request) {
			if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
				$data = json_decode($request->getContent(), true);
				$request->request->replace(is_array($data) ? $data : array());
			}
		});
	}
}