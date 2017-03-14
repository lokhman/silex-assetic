<?php
/**
 * Tools for Silex 2+ framework.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 *
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

namespace Lokhman\Silex\Console\Command\Assetic;

use Assetic\Asset\AssetCollectionInterface;
use Assetic\Asset\AssetInterface;
use Assetic\Util\VarUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump command for Assetic library.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 *
 * @link https://github.com/lokhman/silex-assetic
 */
class DumpCommand extends Command
{
    use LockableTrait;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('assetic:dump')
            ->setDescription('Dumps all assets to the filesystem')
            ->addOption('no-debug', null, InputOption::VALUE_NONE, 'Switch debug mode off');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('<error>The command is already running in another process.</error>');

            return 0;
        }

        $app = $this->getApplication()->getContainer();

        $verbose = function ($asset) use ($output) {
            $output->writeln(sprintf('<fg=cyan>%s/%s</>', $asset->getSourceRoot() ?: '[unknown root]',
                $asset->getSourcePath() ?: '[unknown path]'));
        };

        $assetic = $app['assetic'];
        if (false === $dir = realpath($app['assetic.options']['output_dir'])) {
            $dir = $app['assetic.options']['output_dir'];
        }

        if ($input->getOption('no-debug')) {
            $assetic->setDebug(false);
        }

        $output->writeln(sprintf('Dumping all assets to <comment>%s</comment>.', $dir));
        $output->writeln(sprintf('Debug mode is <comment>%s</comment>.'.PHP_EOL, $assetic->isDebug() ? 'on' : 'off'));

        $app['assetic.dump'](true, function (AssetInterface $asset) use ($output, $verbose) {
            $path = VarUtils::resolve($asset->getTargetPath(), $asset->getVars(), $asset->getValues());
            $output->writeln(sprintf('<comment>%s</comment> <info>[file+]</info> %s', date('H:i:s'), $path));

            if ($output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
                if ($asset instanceof AssetCollectionInterface) {
                    foreach ($asset as $leaf) {
                        $verbose($leaf);
                    }
                } else {
                    $verbose($asset);
                }
            }
        });
    }
}
