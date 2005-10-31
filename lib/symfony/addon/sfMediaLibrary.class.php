<?php

/*
 * This file is part of the symfony package.
 * (c) 2004, 2005 Fabien Potencier <fabien.potencier@symfony-project>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id: sfMediaLibrary.class.php 391 2005-08-30 09:26:44Z fabien $
 */

/**
 *
 * sfMediaLibrary class.
 *
 * @package    symfony.runtime.addon
 * @author     Fabien Potencier <fabien.potencier@symfony-project>
 * @version    SVN: $Id: sfMediaLibrary.class.php 391 2005-08-30 09:26:44Z fabien $
 */
require_once 'model/SfMediaCategory.php';
require_once 'model/SfMedia.php';

class sfMediaLibrary
{
  public function __construct()
  {
  }

  public static function renameFile($fileId, $name)
  {
    $file = sfMediaPeer::retrieveByPK($fileId);
    if ($file instanceof sfMedia)
    {
      $parentPath = SfMediaLibrary::getPathFromIdCategory($file->getIdMediaCategory());
      $basePath = SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath;
      if (!file_exists($basePath.DIRECTORY_SEPARATOR.$name))
      {
        rename($basePath.DIRECTORY_SEPARATOR.$file->getName(), $basePath.DIRECTORY_SEPARATOR.$name);
        rename($basePath.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$file->getName(), $basePath.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$name);

        $file->setName($name);
        $file->setFile($name);
        $file->save();
      }

      return $file->getIdMediaCategory();
    }

    return $dirId;
  }

  public static function renameDir($dirId, $name)
  {
    $dir = sfMediaCategoryPeer::retrieveByPK($dirId);
    if ($dir instanceof sfMediaCategory)
    {
      $parentPath = SfMediaLibrary::getPathFromIdCategory($dir->getIdParent());
      $basePath = SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath;
      if (!file_exists($basePath.DIRECTORY_SEPARATOR.$name))
      {
        rename($basePath.DIRECTORY_SEPARATOR.$dir->getName(), $basePath.DIRECTORY_SEPARATOR.$name);

        $dir->setName($name);
        $dir->save();
      }

      return $dir->getIdParent();
    }

    return $dirId;
  }

  public static function deleteDir($dirId)
  {
    $dir = SfMediaCategoryPeer::retrieveByPK($dirId);
    if ($dir instanceof SfMediaCategory)
    {
      // On ne fait rien si on a des sous-répertoires ou des fichiers dans le répertoire
      $c = new Criteria();
      $c->add(SfMediaPeer::ID_MEDIA_CATEGORY, $dirId);
      $nbFiles = SfMediaPeer::doCount($c);

      $c = new Criteria();
      $c->add(SfMediaCategoryPeer::ID_PARENT, $dirId);
      $nbDirs = SfMediaCategoryPeer::doCount($c);

      if (!$nbDirs && !$nbFiles)
      {
        $parentPath = SfMediaLibrary::getPathFromIdCategory($dir->getIdParent());
        rmdir(SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath.DIRECTORY_SEPARATOR.$dir->getName().DIRECTORY_SEPARATOR.'thumbs');
        rmdir(SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath.DIRECTORY_SEPARATOR.$dir->getName());
        $dir->delete();
      }

      return $dir->getIdParent();
    }
    else
      return 0;
  }

  public static function deleteFile($fileId)
  {
    $file = SfMediaPeer::retrieveByPK($fileId);
    if ($file instanceof SfMedia)
    {
      $parentPath = SfMediaLibrary::getPathFromIdCategory($file->getIdMediaCategory());
      @unlink(SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath.DIRECTORY_SEPARATOR.$file->getFile());
      @unlink(SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$file->getFile());
      $file->delete();

      return $file->getIdMediaCategory();
    }
    else
    {
      return 0;
    }
  }

  public static function createFile($req, $parentId = 0)
  {
    if ($req->hasFile('file') && !$req->hasFileError('file'))
    {
      if (preg_match('~(.+?)\.(\w{2,4})$~', $req->getFileName('file'), $match))
      {
        $f = $match[1];
        $ext = $match[2];
      }
      else
      {
        $f = $req->getFileName('file');
        $ext = '';
      }
      $file = new SfMedia();
      $fileName = SfMediaLibrary::sanitize($f).'.'.$ext;
      $file->setFile($fileName);
      $file->setName($fileName);
      $file->setIdMediaCategory($parentId);

      $parentPath = SfMediaLibrary::getPathFromIdCategory($parentId);
      $upload_dir = SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath;

      if (!file_exists($upload_dir.DIRECTORY_SEPARATOR.$fileName))
      {
        require_once 'sf/sfControl/sfUploadControl.class.php';

        if (!file_exists($upload_dir.DIRECTORY_SEPARATOR.'thumbs')) mkdir($upload_dir.DIRECTORY_SEPARATOR.'thumbs', 0777);

        // We get mimetype
        $file->setMime($req->getFileType('file'));

        // We upload the new file
        $req->moveFile('file', $upload_dir.DIRECTORY_SEPARATOR.$fileName);
  
        // We create a thumbnail for this image
        if ($ext == 'jpg' || $ext == 'png' || $ext == 'gif')
        {
          try
          {
            $thumb = new sfThumbnail(64, 64, false, false);
            $thumb->loadFile($upload_dir.DIRECTORY_SEPARATOR.$fileName);
            $thumb->save($upload_dir.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$fileName);
          }
          catch (Exception $e)
          {
            copy($upload_dir.DIRECTORY_SEPARATOR.$fileName, $upload_dir.DIRECTORY_SEPARATOR.'thumbs'.DIRECTORY_SEPARATOR.$fileName);
          }
        }

        $stats = stat($upload_dir.DIRECTORY_SEPARATOR.$fileName);
        $file->setSize($stats[7]);
        $file->save();
  
        return $file->getId();
      }
    }

    return 0;
  }

  public static function createDir($dir, $parentId = 0)
  {
    $category = new SfMediaCategory();
    $dirName = SfMediaLibrary::sanitize($dir);
    $parentPath = SfMediaLibrary::getPathFromIdCategory($parentId);
    if (!file_exists(SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$parentPath.DIRECTORY_SEPARATOR.$dirName))
    {
      $category->setName($dirName);
      $category->setIdParent($parentId);
      $category->save();

      $path = SfMediaLibrary::getPathFromIdCategory($category->getId());
      mkdir(SF_UPLOAD_DIR.DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.$path, 0777);
    }
    else
      return 0;

    return $category->getId();
  }

  public static function getPathFromIdCategory($dir)
  {
    if ($dir == 0) return '/';

    $path = '';
    $idParent = $dir;
    while ($idParent != 0)
    {
      $category = SfMediaCategoryPeer::retrieveByPK($idParent);
      $idParent = $category->getIdParent();
      $dir = $category->getId();
      $path = $category->getName().'/'.$path;
    }

    return '/'.$path;
  }

  public static function sanitize($input)
  {
    return preg_replace('/[^a-z0-9]+/i', '_', strtolower($input));
  }
}

?>