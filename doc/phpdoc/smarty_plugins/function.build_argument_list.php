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
 * Builds a method argument list.
 *
 * @param array  An array of parameters.
 * @param Smarty A Smarty class instance.
 *
 * @return string A string representing a method argument list.
 */
function smarty_function_build_argument_list ($params, &$smarty)
{

    $args     = '';
    $optional = 0;
    $style    = 'horizontal';

    if (isset($params['style']))
    {

        $style = $params['style'];

    }

    if (isset($params['args']) && $params['args'] != null &&
        count($params['args']) > 0)
    {

        // loop through all arguments
        foreach ($params['args'] as $arg)
        {

            $type = $arg['type'];

            if ($args == '')
            {

                // first loop
                if ($arg['hasdefault'] == 1)
                {

                    // argument is optional
                    $args .= ' [ ' . $type;

                    // increment the optional count
                    $optional++;

                } else
                {

                    // argument is optional
                    $args .= $type;

                }

            } else
            {

                // sequential loop
                if ($style == 'vertical')
                {

                    if ($arg['hasdefault'] == 1)
                    {

                        // argument is optional
                        $args .= '<br/>&nbsp;&nbsp;[, ' . $type;

                        // increment the optional count
                        $optional++;

                    } else
                    {

                        // argument is optional
                        $args .= ',<br/>&nbsp;&nbsp;' . $type;

                    }

                } else
                {

                    if ($arg['hasdefault'] == 1)
                    {

                        // argument is optional
                        $args .= ' [, ' . $type;

                        // increment the optional count
                        $optional++;

                    } else
                    {

                        // argument is optional
                        $args .= ', ' . $type;

                    }

                }

            }

            // append the name to the type
            $args .= ' ' . str_replace('&', '&amp;', $arg['name']);

        }

        if ($optional > 0)
        {

            $args .= ' ' . str_repeat(']', $optional);

        }

    } else
    {

        $args = 'void';

    }

    $args = '&nbsp;(' . $args . ')';

    return $args;

}

?>