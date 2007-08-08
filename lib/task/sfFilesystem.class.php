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
    $logger = null;

  public function __construct(sfLogger $logger = null)
  {
    $this->logger = $logger;
  }

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

  public function mkdirs($path, $mode = 0777)
  {
    if (is_dir($path))
    {
      return true;
    }

    $this->log('dir+', $path);

    return @mkdir($path, $mode, true);
  }

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

    return $this;
  }

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

    return $this;
  }

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

    return $this;
  }

  public function rename($origin, $target, $options = array())
  {
    // we check that target does not exist
    if (is_readable($target))
    {
      throw new sfException(sprintf('Cannot rename because the target "%" already exist.', $target));
    }

    $this->log('rename', $origin.' > '.$target);
    rename($origin, $target);
  }

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

  public function log($section, $text, $size = null)
  {
    if (is_null($this->logger))
    {
      return;
    }

    if ($this->logger instanceof sfCommandLogger)
    {
      $this->logger->log($this->logger->formatSection($section, $text, $size));
    }
    else
    {
      $this->logger->log($section.' '.$text."\n");
    }
  }
}
