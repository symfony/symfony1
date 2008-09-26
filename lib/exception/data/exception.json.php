{
  'error':
  {
    'code':    500,
    'message': 'Internal Server Error',
    'debug':
    {
      'name':   '<?php echo $name ?>',
      'message':'<?php echo addcslashes($message, "\0..\37\\'\"\177..\377\/") ?>'
    }
  }
}
