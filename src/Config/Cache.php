<?php

namespace Skel\Config;

class Cache implements \ArrayAccess
{
    protected $storage = [];
    protected $path;
    protected $filename;
    protected $toSave = false;

    public function __construct($path, $filename)
    {
        $this->path = $path;
        $this->filename = $filename;
        $this->load();
    }

    protected function opcacheEnabled()
    {
        return  function_exists('opcache_compile_file') &&
                ini_get('opcache.enable') && (
                    'cli' !== PHP_SAPI ||
                    ini_get('opcache.enable_cli')
                );
    }

    public function load()
    {
        if(file_exists($this->path.'/'.$this->filename)){
            $this->storage = include($this->path.'/'.$this->filename);
        }
    }

    public function save()
    {
        if($this->toSave){
            file_put_contents(
                $this->path.'/'.$this->filename,
                '<?php'.PHP_EOL.'return '.var_export($this->storage, true).';',
                LOCK_EX
            );

            if($this->opcacheEnabled()){
                @opcache_compile_file($this->path.'/'.$this->filename);
            }

            return true;
        }

        return false;
    }

    public function cleanup()
    {
        $ok = true;
        foreach(glob($this->path.'/*.php') as $filename){
            $ok = $ok && @unlink($filename);
        }

        return $ok;
    }

    public function offsetExists($offset)
    {
        return isset($this->storage[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->storage[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->toSave = true;
        $this->storage[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->toSave = true;
        unset($this->storage[$offset]);
    }
}