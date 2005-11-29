<?php

function smarty_function_sortfilelist ($params, &$smarty)
{
    $varValue  = $smarty->get_template_vars('classleftindex');
    ksort($varValue);

    $smarty->assign('classleftindex', $varValue);
}

?>