<?php echo sprintf('<?xml version="1.0" encoding="%s" ?>', sfConfig::get('sf_charset', 'UTF-8')) ?>
<error code="<?php echo $code ?>" message="<?php echo $text ?>" />
