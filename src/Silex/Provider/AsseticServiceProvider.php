<?php
/**
 * Tools for Silex 2+ framework.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 * @link https://github.com/lokhman/silex-tools
 *
 * Copyright (c) 2016 Alexander Lokhman <alex.lokhman@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Lokhman\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Application;
use Assetic\AssetWriter;
use Assetic\AssetManager;
use Assetic\FilterManager;
use Assetic\Factory\AssetFactory;
use Assetic\Factory\LazyAssetManager;
use Assetic\Extension\Twig\AsseticExtension;
use Assetic\Extension\Twig\TwigFormulaLoader;
use Assetic\Extension\Twig\TwigResource;
use Assetic\Asset\AssetCache;
use Assetic\Asset\AssetCollection;
use Assetic\Cache\FilesystemCache;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Lokhman\Silex\Provider\Assetic\RoutingWorker;
use Lokhman\Silex\Provider\Assetic\MimeTypeGuesser;

/**
 * Silex service provider for Assetic library.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 * @link https://github.com/lokhman/silex-assetic
 */
class AsseticServiceProvider implements ServiceProviderInterface, BootableProviderInterface {

    /**
     * {@inheritdoc}
     */
    public function register(Container $app) {
        $app['assetic.options'] = [];
        $app['assetic.options.default'] = [
            'prefix' => '/',
            'input_dir' => '',
            'output_dir' => '',
            'cache_dir' => null,
            'twig_functions' => [],
            'java' => '/usr/bin/java',
            'ruby' => '/usr/bin/ruby',
            'node' => '/usr/bin/node',
            'node_paths' => [],
            'filters' => [],
            'assets' => [],
        ];

        $app['assetic'] = function() use ($app) {
            return $app['assetic.factory'];
        };

        $app['assetic.factory'] = $app->factory(function() use ($app) {
            $defaults = $app['assetic.options.default'];
            $options = array_replace($defaults, $app['assetic.options']);
            $app['assetic.options'] = $options;

            // register asset factory, filter and asset managers
            $factory = new AssetFactory($options['input_dir'], $app['debug']);
            $factory->setFilterManager($app['assetic.filter_manager']);

            $manager = new AssetManager();
            $factory->setAssetManager($manager);

            // set static assets from configuration
            foreach ($options['assets'] as $name => $formula) {
                $manager->set($name, $factory->createAsset(
                    isset($formula['inputs']) ? $formula['inputs'] : [],
                    isset($formula['filters']) ? $formula['filters'] : [],
                    isset($formula['options']) ? $formula['options'] : []
                ));
            }

            /**
             * Cache busting is not included due to buggy implementation in debug mode.
             *
             * if ($options['cache_busting']) {
             *     $factory->addWorker(new CacheBustingWorker());
             * }
             */

            // routing worker is required for translating path
            $factory->addWorker(new RoutingWorker($options['prefix']));

            return $factory;
        });

        $app['assetic.asset_manager'] = function() use ($app) {
            $manager = new LazyAssetManager($app['assetic']);

            if (isset($app['twig'])) {
                $manager->setLoader('twig', new TwigFormulaLoader($app['twig']));
            }

            return $manager;
        };

        // filter factory dependency injection
        $app['assetic.filter_factory.class'] = 'Lokhman\Silex\Provider\Assetic\FilterFactory';

        $app['assetic.filter_factory'] = $app->factory(function() use ($app) {
            return new $app['assetic.filter_factory.class']($app['assetic.options']);
        });

        $app['assetic.filter_manager'] = function() use ($app) {
            $manager = new FilterManager();
            foreach ($app['assetic.options']['filters'] as $name => $options) {
                $manager->set($name, $app['assetic.filter_factory']->register($name, $options));
            }
            return $manager;
        };

        $app['assetic.writer'] = function() use ($app) {
            return new AssetWriter($app['assetic.options']['output_dir']);
        };

        $app['assetic.cache'] = function() use ($app) {
            if ($app['assetic.options']['cache_dir'] !== null) {
                return new FilesystemCache($app['assetic.options']['cache_dir']);
            }
        };

        $app['assetic.normalize_path'] = $app->protect(function($asset) use ($app) {
            // assets work with non absolute paths (should not start with "/")
            $asset->setTargetPath(ltrim($asset->getTargetPath(), '/'));

            // normalize paths in collections
            if ($asset instanceof AssetCollection) {
                foreach ($asset as $leaf) {
                    $app['assetic.normalize_path']($leaf);
                }
            }
        });

        $app['assetic.output'] = $app->protect(function($asset, $write, callable $callback = null) use ($app) {
            $app['assetic.normalize_path']($asset);

            if ($write) {
                // write asset to file system
                $app['assetic.writer']->writeAsset($asset);

                if ($callback !== null) {
                    $callback($asset);
                }
            } else {
                // use asset cache if enabled
                if (null !== $cache = $app['assetic.cache']) {
                    $asset = new AssetCache($asset, $cache);
                }

                // emulate asset delivery with Silex routing
                $app['controllers']->get($asset->getTargetPath(), function() use ($asset) {
                    $mimeType = MimeTypeGuesser::getInstance()->guess($asset->getTargetPath());
                    return new Response($asset->dump(), 200, ['Content-Type' => $mimeType]);
                });
            }
        });

        $app['assetic.dump'] = $app->protect(function($write, callable $callback = null) use ($app) {
            $manager = $app['assetic.asset_manager'];
            $output = $app['assetic.output'];

            if (isset($app['twig'])) {
                // load assets from all Twig templates
                $loader = $app['twig.loader.filesystem'];
                foreach ($loader->getNamespaces() as $namespace) {
                    if (!$paths = $loader->getPaths($namespace)) {
                        continue;
                    }

                    // search for *.twig files and add them as resources
                    foreach (Finder::create()->files()->name('*.twig')->in($paths) as $fileInfo) {
                        $name = '@' . $namespace . '/' . $fileInfo->getRelativePathname();
                        $manager->addResource(new TwigResource($loader, $name), 'twig');
                    }
                }
            }

            // dump assets from manager
            foreach ($manager->getNames() as $name) {
                $asset = $manager->get($name);

                $formula = $manager->getFormula($name);
                if (null === $debug = $formula[2]['debug']) {
                    $debug = $manager->isDebug();
                }

                if ($formula[2]['combine'] || !$debug) {
                    $output($asset, $write, $callback);
                } elseif ($asset instanceof AssetCollection) {
                    foreach ($asset as $leaf) {
                        $output($leaf, $write, $callback);
                    }
                }
            }
        });

        if (isset($app['twig'])) {
            // register Assetic Twig extension
            $app->extend('twig', function($twig) use ($app) {
                $twig->addExtension(new AsseticExtension($app['assetic'], $app['assetic.options']['twig_functions']));
                return $twig;
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app) {
        if ($app['debug']) {
            $app['assetic.dump'](false);
        }
    }

}
