{if $consts || $iconsts}
    <hr size="1" noshade="noshade"/>
    <a name="constant-summary"></a>
    <table class="constant-summary" cellspacing="1">
        <tr>
            <th colspan="3">Constant Summary</th>
        </tr>
        {section name=const loop=$consts}
            <tr>
                <td class="type" nowrap="nowrap">{strip}{include file="_get_constant_type.tpl" const=$consts[const].const_value}{/strip}</td>
                <td class="name"><a href="{$consts[const].id}">{$consts[const].const_name}</a></td>
                <td class="description" width="100%">
                    {$consts[const].sdesc}

                    {if $consts[const].desc}
                        {$consts[const].desc}
                    {/if}
                </td>
            </tr>
        {/section}
    </table>
{/if}