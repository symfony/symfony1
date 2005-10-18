{if $methods}
    {section name=method loop=$methods}
        {if $methods[method].function_name == "__construct"}
            <hr size="1" noshade="noshade"/>
            <a name="constructor-summary"></a>
            <table class="method-summary" cellspacing="1">
                <tr>
                    <th colspan="2">Constructor Summary</th>
                </tr>
                <tr>
                    <td class="type" nowrap="nowrap" width="1%">{strip}
                        {$methods[method].access}
                    {/strip}</td>
                    <td>
                        <div class="declaration">{strip}
                            <a href="{$methods[method].id}">{$methods[method].function_name}</a>
                            {build_argument_list args=$methods[method].ifunction_call.params strip="no"}
                        {/strip}</div>
                        <div class="description">
                            {$methods[method].sdesc}
                        </div>
                    </td>
                </tr>
            </table>
        {/if}
    {/section}
{/if}