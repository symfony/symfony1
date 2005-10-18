<?php

// +---------------------------------------------------------------------------+
// | This file is part of the Mojavi package.                                  |
// | Copyright (c) 2003, 2004 Sean Kerr.                                       |
// |                                                                           |
// | For the full copyright and license information, please view the LICENSE   |
// | file that was distributed with this source code. You can also view the    |
// | LICENSE file online at http://www.mojavi.org.                             |
// +---------------------------------------------------------------------------+

/**
 * Extract an attribute from an XML element.
 *
 * @param array  An array of parameters.
 * @param Smarty A Smarty class instance.
 *
 * @return string The extracted value.
 */
function smarty_function_extract_attribute ($params, &$smarty)
{

    extract($params);

    if (empty($attribute))
    {

        $smarty->trigger_error("extract_attribute: missing 'attribute' parameter");
        return;

    }

    if (empty($element))
    {

        $smarty->trigger_error("extract_attribute: missing 'element' parameter");
        return;

    }

    if (empty($var))
    {

        $smarty->trigger_error("extract_attribute: missing 'var' parameter");
        return;

    }

    if (empty($append))
    {

        $smarty->trigger_error("extract_attribute: missing 'append' parameter");
        return;

    }

    preg_match('/^<.*?' . $attribute . '="(.*?)".*?>/i', $element, $match);

    $value = $match[1];

    $varValue = $smarty->get_template_vars($var);

    if (strtolower($append) == 'yes')
    {

        $varValue .= $value;

    } else
    {

        $varValue = $value;

    }

    $smarty->assign($var, $value);

}

?>