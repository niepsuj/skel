<?php

define('TEST_ROOT_PATH', __DIR__);
exec('rm -Rf '.TEST_ROOT_PATH.'/Fixtures/tmp/*');

class TestData{
    public static $loader;
}

TestData::$loader = require_once __DIR__ . '/../vendor/autoload.php';



