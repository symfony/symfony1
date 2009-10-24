<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Upgrades Propel.
 *
 * @package    symfony
 * @subpackage task
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */
class sfPropelUpgrade extends sfUpgrade
{
  public function upgrade()
  {
    if (
      file_exists($old = sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php')
      &&
      !file_exists($new = sfConfig::get('sf_lib_dir').'/filter/BaseFormFilterPropel.class.php')
    )
    {
      $this->getFilesystem()->rename($old, $new);
    }

    if (file_exists($file = sfConfig::get('sf_config_dir').'/propel.ini'))
    {
      $original = file_get_contents($file);

      // remove custom builders
      $remove = <<<EOF

; builder settings
propel.builder.peer.class              = plugins.sfPropelPlugin.lib.builder.SfPeerBuilder
propel.builder.object.class            = plugins.sfPropelPlugin.lib.builder.SfObjectBuilder
propel.builder.objectstub.class        = plugins.sfPropelPlugin.lib.builder.SfExtensionObjectBuilder
propel.builder.peerstub.class          = plugins.sfPropelPlugin.lib.builder.SfExtensionPeerBuilder
propel.builder.objectmultiextend.class = plugins.sfPropelPlugin.lib.builder.SfMultiExtendObjectBuilder
propel.builder.mapbuilder.class        = plugins.sfPropelPlugin.lib.builder.SfMapBuilderBuilder

EOF;

      $insert = <<<EOF

; behaviors
propel.behavior.default                        = symfony,symfony_i18n
propel.behavior.symfony.class                  = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfony
propel.behavior.symfony_i18n.class             = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18n
propel.behavior.symfony_i18n_translation.class = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorI18nTranslation
propel.behavior.symfony_behaviors.class        = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorSymfonyBehaviors
propel.behavior.symfony_timestampable.class    = plugins.sfPropelPlugin.lib.behavior.SfPropelBehaviorTimestampable

EOF;

      // remove builders
      $modified = str_replace($remove, '', $original);

      if (false !== strpos($modified, 'plugins.sfPropelPlugin.lib.builder'))
      {
        $temp = sfConfig::get('sf_config_dir').'/propel_new.ini';

        $this->logSection('propel', 'You must update config/propel.ini manually', null, 'ERROR');
        $this->logSection('propel', 'see '.$temp, null, 'ERROR');

        $this->logSection('file+', $temp);
        file_put_contents($temp, $new);
      }
      else
      {
        // add behaviors
        if (false === strpos($modified, 'propel.behavior.symfony'))
        {
          $modified .= $insert;
        }

        if ($original != $modified)
        {
          $this->logSection('file+', $file);
          file_put_contents($file, $modified);
        }
      }
    }
  }
}
