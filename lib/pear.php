<?php

$sf_symfony_lib_dir  = '@PEAR-DIR@/symfony';
$sf_symfony_data_dir = '@DATA-DIR@/symfony';
$sf_version = '@SYMFONY-VERSION@' == '@'.'SYMFONY-VERSION'.'@' ? trim(file_get_contents(dirname(__FILE__).'/BRANCH')) : '@SYMFONY-VERSION@';

return 'OK';
