# silex-assetic

[![StyleCI](https://styleci.io/repos/81963711/shield?branch=master)](https://styleci.io/repos/81963711)

Service provider to support [Assetic](https://github.com/kriswallsmith/assetic) library for
[**Silex 2.0+**](http://silex.sensiolabs.org/) micro-framework.

> This project is a part of [`silex-tools`](https://github.com/lokhman/silex-tools) library.

## <a name="installation"></a>Installation
You can install `silex-assetic` with [Composer](http://getcomposer.org):

    composer require lokhman/silex-assetic

## <a name="documentation"></a>Documentation
Register `AsseticServiceProvider` in your application with the following code:

    use Lokhman\Silex\Provider\AsseticServiceProvider;

    $app->register(new AsseticServiceProvider());

**N.B.:** Assetic will always be in debug mode if `$app['debug']` is `TRUE`.

### Configuration
Configuration is mostly the same as you have for
[AsseticBundle](http://symfony.com/doc/current/reference/configuration/assetic.html) in Symfony framework.

    "assetic.options": {
        "prefix": "/",
        "input_dir": "src",
        "output_dir": "web",
        "cache_dir": "cache/assetic",
        "twig_functions": [],
        "java": "/usr/bin/java",
        "ruby": "/usr/bin/ruby",
        "node": "/usr/bin/node",
        "node_paths": [],
        "filters": {
            "some_filter": {}
        },
        "assets": {
            "some_asset": {
                "inputs": [],
                "filters": [],
                "options": {}
            }
        }
    }

### Filters
Filters are defined in `FilterFactory` class and mirror names and options of
[AsseticBundle](http://symfony.com/doc/current/assetic/asset_management.html#filters).

### Twig
Twig extension is enabled automatically if you have `TwigServiceProvider` registered for the application.

### Console
You can use console command with [`silex-console`](https://github.com/lokhman/silex-console) service provider simply
adding `DumpCommand` to the console application.

    use Lokhman\Silex\Console\Console;
    use Lokhman\Silex\Command\DumpCommand;

    $console = new Console($app);
    $console->add(new DumpCommand());
    $console->run();

### Further reading
For more details please refer to the
[asset management documentation](http://symfony.com/doc/current/assetic/asset_management.html) of Symfony framework.

## <a name="license"></a>License
Library is available under the MIT license. The included LICENSE file describes this in detail.
