<?xml version="1.0" encoding="<?php echo sfConfig::get('sf_charset', 'UTF-8') ?>"?>
<error code="500" message="Internal Server Error">
  <debug>
    <name><?php echo $name ?></name>
    <message><?php echo htmlspecialchars($message, ENT_QUOTES, sfConfig::get('sf_charset', 'UTF-8')) ?></message>
  </debug>
</error>
