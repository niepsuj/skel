<?php

namespace Skel\View;

use Silex\Application 	as SilexApplication;
use Twig_Token          as Token;
use Twig_Node           as Node;
use Twig_TokenStream    as Stream; 
use Twig_Error_Syntax   as ErrorSyntax;
use Twig_TokenParser    as Parser;

class EnvTokenParser extends Parser
{

    protected $app;

    public function __construct(SilexApplication $app)
    {
        $this->app = $app;
    }

    /**
     *  @param Twig_Token $token
     */
    public function parse(Token $token)
    {
        $stream = $this->parser->getStream();
        $container = new Node();
        $this->parseEnv($stream, $container);
        $end = false;
        while(!$end){
            switch($stream->next()->getValue()){
                case 'env':
                    $this->parseEnv($stream, $container);
                    break;
                case 'endenv':
                    $end = true;
                    break;
            }
        }
        $stream->expect(Token::BLOCK_END_TYPE);
        if(count($container) > 1 && $container->hasNode('__default__')){
            $container->removeNode('__default__');
        }
        return $container;
    }

    /**
     * @param Twig_TokenStream $stream
     * @param Twig_Node $container
     */
    public function parseEnv(Stream $stream, Node $container)
    {
        $not = false;
        $condition = null;

        if($stream->test(Token::OPERATOR_TYPE, 'not')){
            $not = true;
            $stream->next();
        }

        if(!$stream->test(Token::BLOCK_END_TYPE)){
            $env = $this->parser->getExpressionParser()->parseExpression()->getAttribute('value');
            if(!preg_match('/^[a-zA-Z_\*]+$/', $env)){
                throw new ErrorSyntax(sprintf('Invalid environment name: "'.$env.'".', 
                    $stream->getCurrent()->getLine()), 
                $stream->getCurrent()->getLine(),
                $stream->getFilename());
            }
            $condition = str_replace('*', '([a-zA-Z_])+?', $env);
        }

        $stream->expect(Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse(function($token){
            return $token->test(array('env', 'endenv'));
        });

        if($not && null === $condition){
            $container->setNode('__default__', $body);
            return;
        }

        $test = preg_match('/^'.$condition.'$/', $this->app['env']);
        if(($not && !$test) || (!$not && $test)){
            $container->setNode($env, $body);
        }
    }

    /**
     * @param String
     */
    public function getTag()
    {
        return 'env';
    }
}