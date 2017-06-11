<?php

define('TEST_ROOT_PATH', __DIR__);
exec('rm -Rf '.TEST_ROOT_PATH.'/Fixtures/tmp/*');

require_once __DIR__ . '/../vendor/autoload.php';

