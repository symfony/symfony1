<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * ValidationHelper.
 *
 * @package    symfony
 * @subpackage helper
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id$
 */

function form_has_error($param)
{
  return sfContext::getInstance()->getRequest()->hasError($param);
}

function form_error($param, $options = array(), $catalogue = 'messages')
{
  $options = _parse_attributes($options);

  $request = sfContext::getInstance()->getRequest();

  $options['controltovalidate'] = $param;
  $options['display'] = 'Dynamic';
  if ($request->hasError($param))
  {
    $options['isvalid'] = 'False';
    $style = '';
  }
  else
  {
    $style = 'display:none;';
  }
  $options['style'] = isset($options['style']) ? $options['style'].';'.$style : $style;
  if (!isset($options['class'])) $options['class'] = 'form_error';
  if (!isset($options['id'])) $options['id'] = 'content:_TRequiredFielValidator';

  $prefix = '&darr;&nbsp;';
  if (isset($options['prefix']))
  {
    $prefix = $options['prefix'];
    unset($options['prefix']);
  }

  $suffix = '&nbsp;&darr;';
  if (isset($options['suffix']))
  {
    $suffix = $options['suffix'];
    unset($options['suffix']);
  }

  $error = $request->getError($param, $catalogue);

  return content_tag('div', $prefix.$error.$suffix, $options)."\n";
}

function add_dynamic_validation()
{
  return <<<EOF
<script type="text/javascript">
<!--
/*<![CDATA[*/
var Page_Validators=new Array(document.getElementById("content:_TRequiredFieldValidator1"), document.getElementById("content:_TCustomValidator2"), document.getElementById("content:_TRequiredFieldValidator3"));
var Validator_Events=new Array("content:_TButton4");
/*]]>*/
// -->
</script>

<script type="text/javascript">
<!--
/*<![CDATA[*/

var Page_ValidationActive = false;
if (typeof(Page_ValidationVer) == "undefined")
  alert("Unable to find script library '/examples/js/prado_validator.js'. Try placing this file manually, or redefine constant JS_VALIDATOR in TValidator.php.");
else if (Page_ValidationVer != "2.00")
  alert("This page uses an incorrect version of '/examples/js/prado_validator.js'. The page expects version 2.00. The script library is " + Page_ValidationVer + ".");
else
  ValidatorOnLoad();

function ValidatorOnSubmit() {
  if (Page_ValidationActive) {
    return ValidatorCommonOnSubmit();
  }
  return true;
}

if (Page_ValidationActive)
{
  for(var i in Validator_Events)
  {
    var obj = document.getElementById(Validator_Events[i]);
    if(obj)
      Validator_addEvent(obj,'click', Page_ClientValidate);
  }
}


/*]]>*/
// -->
</script>
EOF;

}

?>