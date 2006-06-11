<?php

require_once('config/sfDefineEnvironmentConfigHandler.class.php');

class sfDefineEnvironmentConfigHandlerTest extends UnitTestCase
{
  public function test_prefix()
  {
    $handler = new sfDefineEnvironmentConfigHandler();
    $handler->initialize(array('prefix' => 'sf_'));

    $dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR;

    $files = array(
      $dir.'prefix_default.yml',
      $dir.'prefix_all.yml',
    );

    $current_env = sfConfig::get('sf_environment');

    sfConfig::set('sf_environment', 'prod');

    $data = $handler->execute($files);

    $data = preg_replace('#date\: \d+/\d+/\d+ \d+\:\d+\:\d+#', '', $data);

    $this->assertEqual(file_get_contents($dir.'prefix_result.php'), $data);

    sfConfig::set('sf_environment', $current_env);
  }
}
