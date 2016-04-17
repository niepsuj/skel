<?php


namespace Skel;

use Pimple\Container;
use Silex\Provider\TwigServiceProvider;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

use Twig_SimpleFunction;
use Twig_SimpleFilter;
use Twig_Loader_Filesystem;

class ViewProvider extends TwigServiceProvider implements BootableProviderInterface
{

    public function register(Container $app)
    {
        parent::register($app);
        
        $app['twig.path'] = $app['registry'];
        $app['twig.loader.filesystem'] = function ($app) {
        	if(isset($app['view.path'])){
        		$app['twig.path']->push($app['view.path']);
        	}
            return new Twig_Loader_Filesystem($app['twig.path']->flush());
        };
        $app->on(KernelEvents::RESPONSE, 'view.render', 192);

        /**
         * View renderer
         */
        $app['view.render'] = $app->protect(function(FilterResponseEvent $event) use ($app){
            $res = $event->getResponse();
            if($res instanceof ViewResponse){
                $res->setContent(
                    $app['twig']->render( $res->getView().'.twig', $res->flush() )
                );
            }

            return $res;
        });

        $app['view.function.registry'] = $app['registry'];
        $app['view.function'] = $app->protect(function($name, $callback) use ($app){
            $app['view.function.registry']->push(new Twig_SimpleFunction($name, $app['callback_resolver']->resolveCallback($callback)));
            return $app;
        });

        $app['view.filter.registry'] = $app['registry'];
        $app['view.filter'] = $app->protect(function($name, $callback) use ($app){
            $app['view.filter.registry']->push(new Twig_SimpleFilter($name, $app['callback_resolver']->resolveCallback($callback)));
            return $app;
        });

        $app['view'] = $app->protect(function($name, $data = null){
        	$res = new ViewResponse($name);
	        $res->merge($data);
	        return $res;
        });
    }

    public function boot(Application $app)
    {
        foreach($app['view.function.registry']  as $function)   $app['twig']->addFunction($function);
        foreach($app['view.filter.registry']    as $filter)     $app['twig']->addFilter($filter);

        $app['twig']->addTokenParser(new ViewEnvTokenParser($app));
    }
}
