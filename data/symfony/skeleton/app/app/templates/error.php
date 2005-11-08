<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<title>symfony exception</title>
<style type="text/css">

#exception {
    background-color: #EEEEEE;
    border:           solid 1px #750000;
    font-family:      verdana, helvetica, sans-serif;
    font-size:        76%;
    font-style:       normal;
    font-weight:      normal;
    margin:           10px;
}

#help {
    color:     #750000;
    font-size: 0.9em;
}

.message {
    color:       #FF0000;
    font-weight: bold;
}

.title {
    font-size:   1.1em;
    font-weight: bold;
}

td {
    background-color: #EEEEEE;
    padding:          5px;
}

th {
    background-color: #750000;
    color:            #FFFFFF;
    font-size:        1.2em;
    font-weight:      bold;
    padding:          5px;
    text-align:       left;
}

</style>
</head>
<body>

<table id="exception" cellpadding="0" cellspacing="0">
    <tr>
        <th colspan="2"><?php print $name ?></th>
    </tr>
    <tr>
        <td class="title">message:</td>
        <td class="message"><?php print $message ?>
        <?php if ($error_reference): ?>
          <a href='http://www.symfony-project.com/errors/<?php echo $error_reference ?>'>learn more about this issue</a>
        <?php endif ?>
        </td>
    </tr>
    <tr>
        <td class="title">code:</td>
        <td><?php print $code ?></td>
    </tr>
    <tr>
        <td class="title">class:</td>
        <td><?php print $class ?></td>
    </tr>
    <tr>
        <td class="title">file:</td>
        <td><?php print $file ?></td>
    </tr>
    <tr>
        <td class="title">line:</td>
        <td><?php print $line ?></td>
    </tr>
<?php if (count($trace) > 0): ?>
    <tr><th colspan="2">stack trace</th></tr>
    <?php foreach ($trace as $line): ?>
    <tr><td colspan="2"><?php print $line ?></td></tr>
    <?php endforeach ?>
<?php endif ?>

  <tr><th colspan="2">info</th></tr>
    <tr>
        <td class="title">symfony</td>
        <td>v. <?php print SF_APP_VERSION ?></td>
    </tr>
    <tr>
        <td class="title">PHP</td>
        <td>v. <?php print PHP_VERSION ?></td>
    </tr>
    <tr id="help">
        <td colspan="2">for help resolving this issue, please visit <a href="http://www.symfony-project.com/">www.symfony-project.com</a>.</td>
    </tr>
</table>

</body>
</html>