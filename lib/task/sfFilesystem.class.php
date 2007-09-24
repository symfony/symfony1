<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfFilesystem provides basic utility to manipulate the file system.
 *
 * @package    symfony
 * @subpackage util
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfFilesystem
{
  protected
    $dispatcher = null,
    $formatter  = null;

  /**
   * Constructor.
   *
   * @param sfEventDispatcher A sfEventDispatcher instance
   * @param sfFormatter       A sfFormatter instance
   */
  public function __construct(sfEventDispatcher $dispatcher = null, sfFormatter $formatter = null)
  {
    $this->dispatcher = $dispatcher;
    $this->formatter = $formatter;
  }

  /**
   * Copies a file.
   *
   * This method only copies the file if the origin file is newer than the target file.
   *
   * By default, if the target already exists, it is not overriden.
   *
   * To override existing files, pass the "override" option.
   *
   * @param string The original filename
   * @param string The target filename
   * @param array  An array of options
   */
  public function copy($originFile, $targetFile, $options = array())
  {
    if (!array_key_exists('override', $options))
    {
      $options['override'] = false;
    }

    // we create target_dir if needed
    if (!is_dir(dirname($targetFile)))
    {
      $this->mkdirs(dirname($targetFile));
    }

    $mostRecent = false;
    if (file_exists($targetFile))
    {
      $statTarget = stat($targetFile);
      $stat_origin = stat($originFile);
      $mostRecent = ($stat_origin['mtime'] > $statTarget['mtime']) ? true : false;
    }

    if ($options['override'] || !file_exists($targetFile) || $mostRecent)
    {
      $this->log('file+', $targetFile);
      copy($originFile, $targetFile);
    }
  }

  /**
   * Creates a directory recursively.
   *
   * @param  string  The directory path
   * @param  integer The directory mode
   *
   * @return true if the directory has been created, false otherwise
   */
  public function mkdirs($path, $mode = 0777)
  {
    if (is_dir($path))
    {
      return true;
    }

    $this->log('dir+', $path);

    return @mkdir($path, $mode, true);
  }

  /**
   * Creates an empty file.
   *
   * @param string The filename
   */
  public function touch($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    foreach ($files as $file)
    {
      $this->log('file+', $file);

      touch($file);
    }
  }

  /**
   * Removes files or directories.
   *
   * @param array An array of files to remove
   */
  public function remove($files)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    $files = array_reverse($files);
    foreach ($files as $file)
    {
      if (is_dir($file) && !is_link($file))
      {
        $this->log('dir-', $file);

        rmdir($file);
      }
      else
      {
        $this->log(is_link($file) ? 'link-' : 'file-', $file);

        unlink($file);
      }
    }
  }

  /**
   * Change mode for an array of files or directories.
   *
   * @param array   An array of files or directories
   * @param integer The new mode
   * @param integer The mode mask
   */
  public function chmod($files, $mode, $umask = 0000)
  {
    $currentUmask = umask();
    umask($umask);

    if (!is_array($files))
    {
      $files = array($files);
    }

    foreach ($files as $file)
    {
      $this->log(sprintf('chmod %o', $mode), $file);
      chmod($file, $mode);
    }

    umask($currentUmask);
  }

  /**
   * Renames a file.
   *
   * @param string The origin filename
   * @param string The new filename
   */
  public function rename($origin, $target)
  {
    // we check that target does not exist
    if (is_readable($target))
    {
      throw new sfException(sprintf('Cannot rename because the target "%" already exist.', $target));
    }

    $this->log('rename', $origin.' > '.$target);
    rename($origin, $target);
  }

  /**
   * Creates a symbolic link or copy a directory.
   *
   * @param string  The origin directory path
   * @param string  The symbolic link name
   * @param Boolean Whether to copy files if on windows
   */
  public function symlink($originDir, $targetDir, $copyOnWindows = false)
  {
    if (!function_exists('symlink') && $copyOnWindows)
    {
      $finder = sfFinder::type('any')->ignore_version_control();
      $this->mirror($originDir, $targetDir, $finder);
      return;
    }

    $ok = false;
    if (is_link($targetDir))
    {
      if (readlink($targetDir) != $originDir)
      {
        unlink($targetDir);
      }
      else
      {
        $ok = true;
      }
    }

    if (!$ok)
    {
      $this->log('link+', $targetDir);
      symlink($originDir, $targetDir);
    }
  }

  /**
   * Mirrors a directory to another.
   *
   * @param string   The origin directory
   * @param string   The target directory
   * @param sfFinder A sfFinder instance
   * @param array    An array of options (see copy())
   */
  public function mirror($originDir, $targetDir, $finder, $options = array())
  {
    foreach ($finder->relative()->in($originDir) as $file)
    {
      if (is_dir($originDir.DIRECTORY_SEPARATOR.$file))
      {
        $this->mkdirs($targetDir.DIRECTORY_SEPARATOR.$file);
      }
      else if (is_file($originDir.DIRECTORY_SEPARATOR.$file))
      {
        $this->copy($originDir.DIRECTORY_SEPARATOR.$file, $targetDir.DIRECTORY_SEPARATOR.$file, $options);
      }
      else if (is_link($originDir.DIRECTORY_SEPARATOR.$file))
      {
        $this->symlink($originDir.DIRECTORY_SEPARATOR.$file, $targetDir.DIRECTORY_SEPARATOR.$file);
      }
      else
      {
        throw new sfException(sprintf('Unable to guess "%s" file type.', $file));
      }
    }
  }

  /**
   * Executes a shell command.
   *
   * @param string The command to execute on the shell
   */
  public function sh($cmd)
  {
    $this->log('exec ', $cmd);

    ob_start();
    passthru($cmd.' 2>&1', $return);
    $content = ob_get_contents();
    ob_end_clean();

    if ($return > 0)
    {
      throw new sfException(sprintf('Problem executing command %s', "\n".$content));
    }

    return $content;
  }

  /**
   * Replaces tokens in an array of files.
   *
   * @param array  An array of filenames
   * @param string The begin token delimiter
   * @param string The end token delimiter
   * @param array  An array of token/value pairs
   */
  public function replaceTokens($files, $beginToken, $endToken, $tokens)
  {
    if (!is_array($files))
    {
      $files = array($files);
    }

    foreach ($files as $file)
    {
      $content = file_get_contents($file);
      foreach ($tokens as $key => $value)
      {
        $content = str_replace($beginToken.$key.$endToken, $value, $content, $count);
      }

      $this->log('tokens', $file);

      file_put_contents($file, $content);
    }
  }

  /**
   * Logs a message.
   *
   * @param string  The section name
   * @param string  The message
   * @param integer The maximum size of a line
   */
  protected function log($section, $text, $size = null)
  {
    if (!$this->dispatcher)
    {
      return;
    }

    $message = $this->formatter ? $this->formatter->formatSection($section, $text, $size) : $section.' '.$text."\n";

    $this->dispatcher->notify(new sfEvent($this, 'command.log', array($message)));
  }
}
