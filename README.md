## Config

```php

$app = new Silex\Application([
    'env' => 'production',
    'root.path' => __DIR__
]);

$app->register(Skel\ConfigProvider, [
    ''
]);

```
