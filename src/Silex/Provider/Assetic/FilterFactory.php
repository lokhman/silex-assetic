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

namespace Lokhman\Silex\Provider\Assetic;

use Assetic\Util\FilesystemUtils;

/**
 * Filter factory for Assetic library.
 *
 * @author Alexander Lokhman <alex.lokhman@gmail.com>
 * @link https://github.com/lokhman/silex-assetic
 */
class FilterFactory {

    protected $filters = [
        'autoprefixer'        => 'Assetic\Filter\AutoprefixerFilter',
        'cleancss'            => 'Assetic\Filter\CleanCssFilter',
        'closure_api'         => 'Assetic\Filter\GoogleClosure\CompilerApiFilter',
        'closure_jar'         => 'Assetic\Filter\GoogleClosure\CompilerJarFilter',
        'coffee'              => 'Assetic\Filter\CoffeeScriptFilter',
        'compass'             => 'Assetic\Filter\CompassFilter',
        'csscachebusting'     => 'Assetic\Filter\CssCacheBustingFilter',
        'cssembed'            => 'Assetic\Filter\CssEmbedFilter',
        'cssimport'           => 'Assetic\Filter\CssImportFilter',
        'cssmin'              => 'Assetic\Filter\CssMinFilter',
        'cssrewrite'          => 'Assetic\Filter\CssRewriteFilter',
        'dart'                => 'Assetic\Filter\DartFilter',
        'emberprecompile'     => 'Assetic\Filter\EmberPrecompileFilter',
        'gss'                 => 'Assetic\Filter\GssFilter',
        'handlebars'          => 'Assetic\Filter\HandlebarsFilter',
        'jpegoptim'           => 'Assetic\Filter\JpegoptimFilter',
        'jpegtran'            => 'Assetic\Filter\JpegtranFilter',
        'jsmin'               => 'Assetic\Filter\JSMinFilter',
        'jsminplus'           => 'Assetic\Filter\JSMinPlusFilter',
        'jsqueeze'            => 'Assetic\Filter\JSqueezeFilter',
        'less'                => 'Assetic\Filter\LessFilter',
        'lessphp'             => 'Assetic\Filter\LessphpFilter',
        'minifycsscompressor' => 'Assetic\Filter\MinifyCssCompressorFilter',
        'optipng'             => 'Assetic\Filter\OptiPngFilter',
        'packager'            => 'Assetic\Filter\PackagerFilter',
        'packer'              => 'Assetic\Filter\PackerFilter',
        'phpcssembed'         => 'Assetic\Filter\PhpCssEmbedFilter',
        'pngout'              => 'Assetic\Filter\PngoutFilter',
        'reactjsx'            => 'Assetic\Filter\ReactJsxFilter',
        'roole'               => 'Assetic\Filter\RooleFilter',
        'sass'                => 'Assetic\Filter\Sass\SassFilter',
        'scss'                => 'Assetic\Filter\Sass\ScssFilter',
        'sassphp'             => 'Assetic\Filter\SassphpFilter',
        'scssphp'             => 'Assetic\Filter\ScssphpFilter',
        'sprockets'           => 'Assetic\Filter\SprocketsFilter',
        'stylus'              => 'Assetic\Filter\StylusFilter',
        'typescript'          => 'Assetic\Filter\TypeScriptFilter',
        'uglifycss'           => 'Assetic\Filter\UglifyCssFilter',
        'uglifyjs'            => 'Assetic\Filter\UglifyJsFilter',
        'uglifyjs2'           => 'Assetic\Filter\UglifyJs2Filter',
        'yui_css'             => 'Assetic\Filter\Yui\CssCompressorFilter',
        'yui_js'              => 'Assetic\Filter\Yui\JsCompressorFilter',
    ];

    protected $options;

    public function __construct(array $options = []) {
        $this->options = $options;
    }

    protected function getOption(array $options, $name, $default = null) {
        if (array_key_exists($name, $options)) {
            return $options[$name];
        } elseif (func_num_args() > 2) {
            return $default;
        } else {
            throw new \InvalidArgumentException(sprintf('Option "%s" is required.', $name));
        }
    }

    protected function getJavaBin() {
        return $this->getOption($this->options, 'java');
    }

    protected function getNodeBin() {
        return $this->getOption($this->options, 'node');
    }

    protected function getRubyBin() {
        return $this->getOption($this->options, 'ruby');
    }

    protected function getNodePaths() {
        return $this->getOption($this->options, 'node_paths', []);
    }

    protected function getCacheDir() {
        return $this->getOption($this->options, 'cache_dir', FilesystemUtils::getTemporaryDirectory());
    }

    protected function getFilterClass($name) {
        if (strpos($name, '_') === 0) {
            $name = substr($name, 1);
        }

        return $this->filters[$name];
    }

    public function register($name, $options) {
        if (!method_exists($this, $method = '_' . $name)) {
            throw new \RuntimeException(sprintf('There is no "%s" filter.', $name));
        }

        if (!is_array($options)) {
            $options = [];
        }

        return $this->$method($options);
    }

    protected function _autoprefixer(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/autoprefixer'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setBrowsers($this->getOption($options, 'browsers', []));
        return $filter;
    }

    protected function _cleancss(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/cleancss'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setKeepLineBreaks($this->getOption($options, 'keep_line_breaks', false));
        $filter->setRemoveSpecialComments($this->getOption($options, 'remove_special_comments', false));
        $filter->setOnlyKeepFirstSpecialComment($this->getOption($options, 'only_keep_first_special_comment', true));
        $filter->setSemanticMerging($this->getOption($options, 'set_semantic_merging', false));
        $filter->setRootPath($this->getOption($options, 'root_path', null));
        $filter->setSkipImport($this->getOption($options, 'skip_import', true));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setSkipRebase($this->getOption($options, 'skip_rebase', true));
        $filter->setSkipRestructuring($this->getOption($options, 'skip_restructuring', true));
        $filter->setSkipShorthandCompacting($this->getOption($options, 'skip_shorthand_compacting', true));
        $filter->setSourceMap($this->getOption($options, 'source_map', false));
        $filter->setSourceMapInlineSources($this->getOption($options, 'source_map_inline_sources', false));
        $filter->setSkipAdvanced($this->getOption($options, 'skip_advanced', true));
        $filter->setSkipAggresiveMerging($this->getOption($options, 'skip_aggresive_merging', true));
        $filter->setSkipImportFrom($this->getOption($options, 'skip_import_from', null));
        $filter->setMediaMerging($this->getOption($options, 'media_merging', true));
        $filter->setRoundingPrecision($this->getOption($options, 'rounding_precision', null));
        $filter->setCompatibility($this->getOption($options, 'compatibility', false));
        $filter->setDebug($this->getOption($options, 'debug', false));
        return $filter;
    }

    protected function _closure_api(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setCompilationLevel($this->getOption($options, 'compilation_level', null));
        $filter->setLanguage($this->getOption($options, 'language_in', null));
        $filter->setFormatting($this->getOption($options, 'formatting', null));
        $filter->setWarningLevel($this->getOption($options, 'warning_level', null));
        return $filter;
    }

    protected function _closure_jar(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'jar'), $this->getOption($options, 'java', $this->getJavaBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setCompilationLevel($this->getOption($options, 'compilation_level', null));
        $filter->setLanguage($this->getOption($options, 'language_in', null));
        $filter->setFormatting($this->getOption($options, 'formatting', null));
        $filter->setWarningLevel($this->getOption($options, 'warning_level', null));
        return $filter;
    }

    protected function _coffee(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/coffee'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setBare($this->getOption($options, 'bare', null));
        $filter->setNoHeader($this->getOption($options, 'no_header', null));
        return $filter;
    }

    protected function _compass(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/compass'), $this->getOption($options, 'ruby', $this->getRubyBin()));
        $filter->setScss($this->getOption($options, 'scss', null));
        $filter->setUnixNewlines($this->getOption($options, 'unix_newlines', null));
        $filter->setNoCache($this->getOption($options, 'no_cache', null));
        $filter->setForce($this->getOption($options, 'force', null));
        $filter->setQuiet($this->getOption($options, 'quiet', null));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setDebugInfo($this->getOption($options, 'debug', false));
        $filter->setBoring($this->getOption($options, 'boring', true));
        $filter->setNoLineComments($this->getOption($options, 'no_line_comments', false));
        $filter->setStyle($this->getOption($options, 'style', null));
        $filter->setImagesDir($this->getOption($options, 'images_dir', null));
        $filter->setFontsDir($this->getOption($options, 'fonts_dir', null));
        $filter->setRelativeAssets($this->getOption($options, 'relative_assets', false));
        $filter->setJavascriptsDir($this->getOption($options, 'javascripts_dir', null));
        $filter->setHttpPath($this->getOption($options, 'http_path', null));
        $filter->setHttpImagesPath($this->getOption($options, 'http_images_path', null));
        $filter->setHttpFontsPath($this->getOption($options, 'http_fonts_path', null));
        $filter->setHttpGeneratedImagesPath($this->getOption($options, 'http_generated_images_path', null));
        $filter->setGeneratedImagesPath($this->getOption($options, 'generated_images_path', null));
        $filter->setHttpJavascriptsPath($this->getOption($options, 'http_javascripts_path', null));
        $filter->setPlugins($this->getOption($options, 'plugins', []));
        $filter->setLoadPaths($this->getOption($options, 'load_paths', []));
        $filter->setHomeEnv($this->getOption($options, 'home_env', true));
        $filter->setCacheLocation($this->getOption($options, 'cache_location', null));
        return $filter;
    }

    protected function _csscachebusting(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setVersion($this->getOption($options, 'version', null));
        $filter->setFormat($this->getOption($options, 'format', '%s?%s'));
        return $filter;
    }

    protected function _cssembed(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'jar'), $this->getOption($options, 'java', $this->getJavaBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setCharset($this->getOption($options, 'charset', 'utf8'));
        $filter->setMhtml($this->getOption($options, 'mhtml', false));
        $filter->setMhtmlRoot($this->getOption($options, 'mhtml_root', null));
        $filter->setRoot($this->getOption($options, 'root', null));
        $filter->setSkipMissing($this->getOption($options, 'skip_missing', false));
        $filter->setMaxUriLength($this->getOption($options, 'max_uri_length', null));
        $filter->setMaxImageSize($this->getOption($options, 'max_image_size', null));
        return $filter;
    }

    protected function _cssimport() {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class();
    }

    protected function _cssmin(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setFilters($this->getOption($options, 'filters', []));
        $filter->setPlugins($this->getOption($options, 'plugins', []));
        return $filter;
    }

    protected function _cssrewrite() {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class();
    }

    protected function _dart(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/dart2js'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        return $filter;
    }

    protected function _emberprecompile(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/ember-precompile'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        return $filter;
    }

    protected function _gss(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'jar'), $this->getOption($options, 'java', $this->getJavaBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setAllowUnrecognizedFunctions($this->getOption($options, 'allow_unrecognized_functions', null));
        $filter->setAllowedNonStandardFunctions($this->getOption($options, 'allowed_non_standard_functions', null));
        $filter->setCopyrightNotice($this->getOption($options, 'copyright_notice', null));
        $filter->setDefine($this->getOption($options, 'define', null));
        $filter->setGssFunctionMapProvider($this->getOption($options, 'gss_function_map_provider', null));
        $filter->setInputOrientation($this->getOption($options, 'input_orientation', null));
        $filter->setOutputOrientation($this->getOption($options, 'output_orientation', null));
        $filter->setPrettyPrint($this->getOption($options, 'pretty_print', null));
        return $filter;
    }

    protected function _handlebars(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/handlebars'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setMinimize($this->getOption($options, 'minimize', false));
        $filter->setSimple($this->getOption($options, 'simple', false));
        return $filter;
    }

    protected function _jpegoptim(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/jpegoptim'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setStripAll($this->getOption($options, 'strip_all', false));
        $filter->setMax($this->getOption($options, 'max', null));
        return $filter;
    }

    protected function _jpegtran(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/jpegtran'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setCopy($this->getOption($options, 'copy', null));
        $filter->setOptimize($this->getOption($options, 'optimize', false));
        $filter->setProgressive($this->getOption($options, 'progressive', false));
        $filter->setRestart($this->getOption($options, 'restart', null));
        return $filter;
    }

    protected function _jsmin() {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class();
    }

    protected function _jsminplus() {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class();
    }

    protected function _jsqueeze(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setSingleLine($this->getOption($options, 'single_line', true));
        $filter->keepImportantComments($this->getOption($options, 'keep_important_comments', true));
        $filter->setSpecialVarRx($this->getOption($options, 'special_var_rx', false));
        return $filter;
    }

    protected function _less(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'node', $this->getNodeBin()), $this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setCompress($this->getOption($options, 'compress', null));
        $filter->setLoadPaths($this->getOption($options, 'load_paths', []));
        return $filter;
    }

    protected function _lessphp(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setPresets($this->getOption($options, 'presets', []));
        $filter->setLoadPaths($this->getOption($options, 'paths', []));
        /**
         * "formatter" can be set to one of: "lessjs", "compressed", "classic".
         * See http://leafo.net/lessphp/docs/#output_formatting
         */
        $filter->setFormatter($this->getOption($options, 'formatter', null));
        $filter->setPreserveComments($this->getOption($options, 'preserve_comments', null));
        return $filter;
    }

    protected function _minifycsscompressor() {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class();
    }

    protected function _optipng(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/optipng'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setLevel($this->getOption($options, 'level', null));
        return $filter;
    }

    protected function _packager(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'packages', []));
        return $filter;
    }

    protected function _packer(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setFastDecode($this->getOption($options, 'fast_decode', true));
        $filter->setSpecialChars($this->getOption($options, 'special_chars', false));
        $filter->setEncoding($this->getOption($options, 'encoding', 'None'));
        return $filter;
    }

    protected function _phpcssembed() {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class();
    }

    protected function _pngout(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/pngout'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setColor($this->getOption($options, 'color', null));
        $filter->setFilter($this->getOption($options, 'filter', null));
        $filter->setStrategy($this->getOption($options, 'strategy', null));
        $filter->setBlockSplitThreshold($this->getOption($options, 'block_split_threshold', null));
        return $filter;
    }

    protected function _reactjsx(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        return new $class($this->getOption($options, 'bin', '/usr/bin/jsx'), $this->getOption($options, 'node', $this->getNodeBin()));
    }

    protected function _roole(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/roole'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        return $filter;
    }

    protected function _sass(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/sass'), $this->getOption($options, 'ruby', $this->getRubyBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setStyle($this->getOption($options, 'style', null));
        $filter->setCompass($this->getOption($options, 'compass', null));
        $filter->setLoadPaths($this->getOption($options, 'load_paths', []));
        $filter->setCacheLocation($this->getOption($options, 'cache_location', $this->getCacheDir()));
        $filter->setSourceMap($this->getOption($options, 'enable_sourcemaps', null));
        $filter->setPrecision($this->getOption($options, 'precision', null));
        return $filter;
    }

    protected function _scss(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/sass'), $this->getOption($options, 'ruby', $this->getRubyBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setStyle($this->getOption($options, 'style', null));
        $filter->setCompass($this->getOption($options, 'compass', null));
        $filter->setLoadPaths($this->getOption($options, 'load_paths', []));
        $filter->setCacheLocation($this->getOption($options, 'cache_location', $this->getCacheDir()));
        $filter->setSourceMap($this->getOption($options, 'enable_sourcemaps', null));
        $filter->setPrecision($this->getOption($options, 'precision', null));
        return $filter;
    }

    protected function _sassphp(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->setOutputStyle($this->getOption($options, 'output_style', null));
        $filter->setIncludePaths($this->getOption($options, 'include_paths', []));
        return $filter;
    }

    protected function _scssphp(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class();
        $filter->enableCompass($this->getOption($options, 'compass', false));
        $filter->setImportPaths($this->getOption($options, 'import_paths', []));
        $filter->setVariables($this->getOption($options, 'variables', []));
        $filter->setFormatter($this->getOption($options, 'formatter', null));
        return $filter;
    }

    protected function _sprockets(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'lib', null), $this->getOption($options, 'ruby', $this->getRubyBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setAssetRoot($this->getOption($options, 'asset_root', null));
        return $filter;
    }

    protected function _stylus(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'node', $this->getNodeBin()), $this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setCompress($this->getOption($options, 'compress', null));
        $filter->setUseNib($this->getOption($options, 'nib', null));
        return $filter;
    }

    protected function _typescript(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/tsc'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        return $filter;
    }

    protected function _uglifycss(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/uglifycss'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setExpandVars($this->getOption($options, 'expand_vars', false));
        $filter->setUglyComments($this->getOption($options, 'ugly_comments', false));
        $filter->setCuteComments($this->getOption($options, 'cute_comments', false));
        return $filter;
    }

    protected function _uglifyjs(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/uglifyjs'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setBeautify($this->getOption($options, 'beautify', false));
        $filter->setNoCopyright($this->getOption($options, 'no_copyright', false));
        $filter->setUnsafe($this->getOption($options, 'unsafe', false));
        $filter->setMangle($this->getOption($options, 'mangle', false));
        $filter->setDefines($this->getOption($options, 'defines', []));
        return $filter;
    }

    protected function _uglifyjs2(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'bin', '/usr/bin/uglifyjs'), $this->getOption($options, 'node', $this->getNodeBin()));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setNodePaths($this->getOption($options, 'node_paths', $this->getNodePaths()));
        $filter->setCompress($this->getOption($options, 'compress', false));
        $filter->setBeautify($this->getOption($options, 'beautify', false));
        $filter->setMangle($this->getOption($options, 'mangle', false));
        $filter->setScrewIe8($this->getOption($options, 'screw_ie8', false));
        $filter->setComments($this->getOption($options, 'comments', false));
        $filter->setWrap($this->getOption($options, 'wrap', false));
        $filter->setDefines($this->getOption($options, 'defines', []));
        return $filter;
    }

    protected function _yui_css(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'jar'), $this->getOption($options, 'java', $this->getJavaBin()));
        $filter->setCharset($this->getOption($options, 'charset', 'utf8'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setStackSize($this->getOption($options, 'stacksize', null));
        $filter->setLineBreak($this->getOption($options, 'linebreak', null));
        return $filter;
    }

    protected function _yui_js(array $options) {
        $class = $this->getFilterClass(__FUNCTION__);
        $filter = new $class($this->getOption($options, 'jar'), $this->getOption($options, 'java', $this->getJavaBin()));
        $filter->setCharset($this->getOption($options, 'charset', 'utf8'));
        $filter->setTimeout($this->getOption($options, 'timeout', null));
        $filter->setStackSize($this->getOption($options, 'stacksize', null));
        $filter->setNomunge($this->getOption($options, 'nomunge', null));
        $filter->setPreserveSemi($this->getOption($options, 'preserve_semi', null));
        $filter->setDisableOptimizations($this->getOption($options, 'disable_optimizations', null));
        $filter->setLineBreak($this->getOption($options, 'linebreak', null));
        return $filter;
    }

}
