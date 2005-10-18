<!-- HEADER -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>symfony API</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<link rel="stylesheet" type="text/css" href="{$subdir}media/common.css"/>
<link rel="stylesheet" type="text/css" href="{$subdir}media/class.css"/>
<link rel="stylesheet" type="text/css" href="{$subdir}media/constants.css"/>
<link rel="stylesheet" type="text/css" href="{$subdir}media/methods.css"/>
</head>
<body>

<table id="header" cellspacing="0">
    <tr>
        <td><img src="{$subdir}media/symfony.gif" width="228" height="34" alt="symfony API"/></td>
        <td id="header-package">
            {if $subpackage}
                <a href="{$subdir}classtrees_{$package}.html">{$package}</a>.{$subpackage}
            {else}
                <a href="{$subdir}classtrees_{$package}.html">{$package}</a>
            {/if}
        </td>
    </tr>
</table>
<!-- /HEADER -->

<p/>

<!-- CONTENT -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td id="class-list" nowrap="nowrap">
            {include file="_class_list.tpl"}
        </td>
        <td id="content">
