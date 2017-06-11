<?php


namespace Skel;

use Pimple\Container;
use Silex\Provider\TwigServiceProvider;
use Silex\Api\BootableProviderInterface;
use Silex\Application;

use Skel\Event\CleanupEvent;
use Skel\Response\ViewResponse;
use Skel\View\EnvTokenParser;
use Skel\View\Service;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;


class ViewProvider extends TwigServiceProvider implements BootableProviderInterface
{

    public function register(Container $app)
    {
        parent::register($app);

        if(!isset($app['view.path'])){
            $app['view.path'] = function($app){
                return $app['root.path'].'/view';
            };
        }

        $app['twig.path'] = function($app){
            return new \ArrayObject([$app['view.path']]);
        };

        $app['twig.loader.filesystem'] = function ($app) {
            return new \Twig_Loader_Filesystem(
                (array) $app['twig.path']
            );
        };

        $app['view.function']   = function(){
            return new \ArrayObject([]);
        };

        $app['view.filter']     = function(){
            return new \ArrayObject([]);
        };

        $app['twig.cache.path'] = function($app){
            if(isset($app['tmp.path'])){
                $dir = $app['tmp.path'] . '/view';
            }else{
                $dir = $app['view.path'] . '/tmp';
            }

            if(!file_exists($dir)){
                mkdir($dir);
            }

            return $dir;
        };

        $app['view'] = function($app){
            $app['twig.options'] = [
                'cache' => $app['twig.cache.path']
            ];

            return new Service($app['twig']);
        };

        /** @var Application $app */
        $app->on(KernelEvents::RESPONSE, function(FilterResponseEvent $responseEvent) use ($app){
            $response = $responseEvent->getResponse();
            if($response instanceof ViewResponse){
                $response->setContent(
                    $app['view']->render($response)
                );
            }

            return $response;
        }, 192);

        if(isset($app['cleanup.scope'])){
            $app['cleanup.scope'][] = 'view';

            $app->on(ProjectEvents::CLEANUP, function(CleanupEvent $cleanupEvent) use ($app){
                if($cleanupEvent->can('view')){
                    $cleanupEvent->report(
                        'view',
                        $app['view']->cleanup()
                    );
                }
            });
        }
    }

    public function boot(Application $app)
    {
        foreach($app['view.function']  as $name => $function) {
            $app['twig']->addFunction(
                new \Twig_SimpleFunction(
                    $name,
                    $app['callback_resolver']->resolveCallback($function)
                )
            );
        }

        foreach($app['view.filter']    as $name => $filter) {
            $app['twig']->addFilter(
                new \Twig_SimpleFilter(
                    $name,
                    $app['callback_resolver']->resolveCallback($filter)
                )
            );
        }

        $app['twig']->addTokenParser(new EnvTokenParser($app));
    }
}
