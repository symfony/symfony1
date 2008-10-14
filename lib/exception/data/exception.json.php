{
  "error":
  {
    "code":    <?php echo $code ?>,
    "message": "<?php echo addcslashes($text, "\0..\37\\'\"\177..\377\/") ?>",
    "debug":
    {
      "name":    "<?php echo $name ?>",
      "message": "<?php echo addcslashes($message, "\0..\37\\'\"\177..\377\/") ?>"
      "traces":
      [
<?php foreach ($traces as $trace): ?>
        "<?php echo addcslashes($trace, "\0..\37\\'\"\177..\377\/") ?>"

<?php endforeach; ?>
      ]
    }
  }
}
