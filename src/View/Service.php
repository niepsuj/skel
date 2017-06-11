<?php

namespace Skel\View;

use Skel\Response\ViewResponse;

class Service
{
    protected $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render($view, array $data = [])
    {
        if($view instanceof ViewResponse){
            return $this->twig->render( $view->getView().'.twig', $view->store());
        }

        return $this->twig->render( $view.'.twig', $data);
    }

    public function response($view, array $data = [])
    {
        $response = new ViewResponse($view);
        $response->merge($data);
        return $response;
    }

    public function cleanup()
    {
        $ok = true;

        foreach (
            new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $this->twig->getCache(),
                    \FilesystemIterator::SKIP_DOTS
                )
            ) as $file
        ) {
            $ok = $ok && ($file->isDir() || @unlink($file));
        }

        return $ok;
    }
}