<?php

namespace cakebake\lesscss;

/**
* LessConverter supports conversion of less script format into CSS script.
* 
* @author cakebake (Jens A.)
* @copyright cakebake (Jens A.)
* @license LGPL-V3
* @uses Less_Parser Class for parsing and compiling less files into css
* 
* @example
*    $less = new \cakebake\lesscss\LessConverter();
*    $less->init([
*        [
*            'input' => __DIR__ . '/example-1.less',
*            'webFolder' => '../tests',
*        ],
*        [
*            'input' => __DIR__ . '/example-2.less',
*            'webFolder' => '../tests',
*        ],
*    ], __DIR__ . '/css/output.css');
*/
class LessConverter
{
    const INPUT_EXT = 'less';

    const OUTPUT_EXT = 'css';

    /**
    * @var bool You can tell less.php to remove comments and whitespace to generate minimized css files.
    */
    public $compress = false;

    /**
    * @var mixed less.php will save serialized parser data for each .less file. Faster, but more memory-intense.
    */
    public $useCache = true;

    /**
    * @var mixed Optional: is passed to the SetCacheDir() method. By default "./cache" is used.
    */
    public $cacheDir = null;

    /**
    * @var null|string Bypass some less to parse multiple
    */
    public $parseString = null;
    
    /**
     * @var bool The sourcemap will be appended to the generated css file
     */
    public $sourceMap = true;

    /**
    * @var bool Force parsing 
    */
    public $forceUpdate = false;

    /**
    * @var obj Less_Parser Cache obj
    */
    private $_parser = null;

    /**
    * @var bool 
    */
    private $_mustRegenerate = false;

    /**
    * Makes the convertion job
    * 
    * @param array $args array('input', 'webFolder')
    * @param string $output The output css file
    */
    public function init($args, $output, $return = false)
    {
        if (!is_array($args)) {
            throw new Exception(__METHOD__ . ': Input $args must be an array.');
        }

        if (!$this->mustRegenerate($args, $output)) {
            if ($return === true && file_exists($output)) {
                return file_get_contents($output);
            }

            return false;
        }

        foreach ($args as $config) {
            extract($config);
            if (file_exists($input)) {
                $this->convert($input, $output, $webFolder);
            }
        }

        if ($this->parseString !== null) {
            $this->_parser->parse($this->parseString);
        }

        if (is_object($this->_parser)) {
            if (($css = $this->_parser->getCss()) && !empty($css)) {
                file_put_contents($output, $css, LOCK_EX);

                return ($return === true) ? $css : true;
            }
        }
    }

    /**
    * Checks all files, if the convertion must be refreshed
    * 
    * @param array $args array('input', 'webFolder')
    * @param string $output The output css file
    */
    public function mustRegenerate($args, $output)
    {
        if ($this->forceUpdate === true)
            return $this->_mustRegenerate = true;

        if ($this->_mustRegenerate === false) {
            foreach ($args as $config) {
                extract($config);
                if (@filemtime("$output") < @filemtime("$input")) {

                    return $this->_mustRegenerate = true;
                }
            }
        }

        return $this->_mustRegenerate = false;
    }

    /**
    * Converts a given LESS assets file into a CSS
    *
    * @param string $input the asset file path, absolute
    * @param string $output the output file path, absolute
    * @param string $webFolder The url root to prepend to any relative image or @import urls in the .less file.
    * @return boolean true on success, false on failure. 
    */
    public function convert($input, $output, $webFolder)
    {
        if (($pos = strrpos($input, '.')) === false)
            return false;

        if (($ext = substr($input, $pos + 1)) !== self::INPUT_EXT)
            return false;

        $this->parseLess($input, $webFolder);

        return false;
    }

    /**
    * Parsing Less File
    *
    * @param string $input the asset file path, absolute
    * @param string $webFolder The url root to prepend to any relative image or @import urls in the .less file.
    * @return mixed string with css on success, false on failure. 
    * @throws Less_Exception_Parser when the command fails
    * @see https://github.com/oyejorge/less.php
    */
    protected function parseLess($input, $webFolder)
    {
        if (!class_exists('Less_Parser')) {
            throw new Exception(__METHOD__ . ': Class Less_Parser does not exist.');
        }

        if ($this->_parser === null) {
            $this->_parser = new \Less_Parser(array(
                'compress' => ($this->compress === true) ? true : false,
                'cache_dir' => $this->getCacheSetting(),
                'sourceMap' => ($this->sourceMap === true) ? true : false,
            ));
        }

        return $this->_parser->parseFile($input, $webFolder);
    }

    /**
    * Get cache settings from config
    * @return string|bool Cache dir path or false
    */
    protected function getCacheSetting()
    {
        return ($this->useCache === true) ? ($this->cacheDir !== null && is_dir($this->cacheDir)) ? $this->cacheDir : __DIR__ . DIRECTORY_SEPARATOR . '/../tmp/cache' : false;
    }
}
