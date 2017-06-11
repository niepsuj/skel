<?php

namespace Skel\Config;

class Service
{
    protected $path = null;
    protected $env = null;
    protected $cache = null;

    /**
     * Service constructor.
     * @param string $path
     * @param string $env
     * @param array|\ArrayAccess $cache
     */
    public function __construct($path, $env = 'production', $cache = null)
    {
        $this->path     = $path;
        $this->env      = $env;
        $this->cache    = is_array($cache) || $cache instanceof \ArrayAccess ? $cache : [];
    }

    protected function resolve($name)
    {
        $base = $this->path.'/'.$name;

        if($this->path !== 'production'){
            $path = $base.'.'.$this->env.'.json';
            if(file_exists($path)){
                return $path;
            }
        }

        $path = $base.'.json';
        if(!file_exists($path)){
            throw new \Exception('Invalid config file: '.$path);
        }

        return $path;
    }

    /**
     * @param string $name File name
     * @param boolean $flatten ['array']['structure'] to ['flat.array.list']
     * @param string|boolean $prefix
     * @return array
     */
    public function load($name, $flatten = false, $prefix = false)
    {
        $cachedName = $name.
            ($flatten?'1':'0').
            (is_string($prefix)?$prefix:($prefix?'1':'0'));

        if(isset($this->cache[$cachedName])){
            return $this->cache[$cachedName];
        }

        $file = $this->resolve($name);
        $raw    = file_get_contents($file);
        $data   = json_decode($raw, true);

        if(!$flatten) {
            $this->cache[$cachedName] = $data;
            return $data;
        }

        $result = [];
        $iterator = new \RecursiveArrayIterator($data);

        $keys = false === $prefix ? [] : [
            true === $prefix ? preg_replace(
                '/([^a-zA-Z0-9_\-]+)/',
                '.',
                $name
            ) : $prefix
        ];

        /**
         * @param \RecursiveArrayIterator $iterator
         */
        $iterate = function($iterator) use (&$keys, &$iterate, &$result){

            while( $iterator->valid() ) {
                if( $iterator->hasChildren() ) {
                    array_push($keys, $iterator->key());
                    $iterate($iterator->getChildren());
                }else{
                    $result[
                        implode('.', $keys).'.'.$iterator->key()
                    ] = $iterator->current();
                }

                $iterator->next();
            }

            array_pop($keys);
        };

        iterator_apply($iterator, $iterate, array($iterator));
        $this->cache[$cachedName] = $result;

        return $result;
    }
}