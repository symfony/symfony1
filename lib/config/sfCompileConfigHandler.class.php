<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfCompileConfigHandler gathers multiple files and puts them into a single file.
 * Upon creation of the new file, all comments and blank lines are removed.
 *
 * @package    symfony
 * @subpackage config
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <skerr@mojavi.org>
 * @version    SVN: $Id$
 */
class sfCompileConfigHandler extends sfYamlConfigHandler
{
  /**
   * Execute this configuration handler.
   *
   * @param array An array of absolute filesystem path to a configuration file.
   *
   * @return string Data to be written to a cache file.
   *
   * @throws sfConfigurationException If a requested configuration file does not exist or is not readable.
   * @throws sfParseException If a requested configuration file is improperly formatted.
   */
  public function execute($configFiles)
  {
    // parse the yaml
    $config = $this->parseYamls($configFiles);

    // init our data
    $data = '';

    // let's do our fancy work
    foreach ($config as $file)
    {
      $file = $this->replaceConstants($file);
      $file = $this->replacePath($file);

      if (!is_readable($file))
      {
        // file doesn't exist
        $error = sprintf('Configuration file "%s" specifies nonexistent or unreadable file "%s"', $configFiles[0], $file);
        throw new sfParseException($error);
      }

      $contents = file_get_contents($file);

      // append file data
      $data .= "\n".$contents;
    }

    // insert configuration files
    $data = preg_replace_callback(array('#(require|include)(_once)?\((sfConfigCache::getInstance\(\)|\$configCache)->checkConfig\([^_]+sf_app_config_dir_name[^\.]*\.\'/([^\']+)\'\)\);#m',
                                        '#()()(sfConfigCache::getInstance\(\)|\$configCache)->import\(.sf_app_config_dir_name\.\'/([^\']+)\'(, false)?\);#m'),
                                  array($this, 'insertConfig'), $data);

    // strip comments (not in debug mode)
    if (!sfConfig::get('sf_debug'))
    {
      $data = sfToolkit::stripComments($data);
    }

    // strip php tags
    $data = sfToolkit::pregtr($data, array('/^\s*<\?(php)?/m' => '',
                                           '/^\s*\?>/m'       => ''));

    // replace windows and mac format with unix format
    $data = str_replace("\r",  "\n", $data);

    // replace multiple new lines with a single newline
    $data = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $data);

    // compile data
    $retval = sprintf("<?php\n".
                      "// auto-generated by sfCompileConfigHandler\n".
                      "// date: %s\n%s\n",
                      date('Y/m/d H:i:s'), $data);

    // save current symfony release
    file_put_contents(sfConfig::get('sf_config_cache_dir').'/VERSION', file_get_contents(sfConfig::get('sf_symfony_lib_dir').'/VERSION'));

    return $retval;
  }

  protected function insertConfig($matches)
  {
    $configFile = sfConfig::get('sf_app_config_dir_name').'/'.$matches[4];

    sfConfigCache::getInstance()->checkConfig($configFile);

    $config = "// '$configFile' config file\n".
              file_get_contents(sfConfigCache::getInstance()->getCacheName($configFile));

    return $config;
  }
}
