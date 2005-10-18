{if $methods && (count($methods) > 1 ||
   ($methods[0].function_name != "__construct" &&
    $methods[0].function_name != "__destruct"))}

    <hr size="1" noshade="noshade"/>
    <a name="method-details"></a>
    <table class="method-details" cellspacing="1">
        <tr>
            <th>Method Details</th>
        </tr>
        {section name=method loop=$methods}
            {if $methods[method].function_name != "__construct" &&
                $methods[method].function_name != "__destruct"}

                <tr>
                    <td class="method-data">
                        {if trim(substr($methods[method].function_call, 0, 1)) == "&"}
                            {assign var="ref" value="true"}
                        {else}
                            {assign var="ref" value="false"}
                        {/if}

                        <a name="{$methods[method].method_dest}"></a>

                        <h2>{$methods[method].function_name}</h2>

                        <table class="method-detail" cellspacing="0">
                            <tr>
                                <td nowrap="nowrap">{strip}
                                    {if $methods[method].access == "protected"}
                                        protected&nbsp;
                                    {/if}

                                    {if $methods[method].access == "public"}
                                        public&nbsp;
                                    {/if}

                                    {if $methods[method].abstract == 1}
                                        abstract&nbsp;
                                    {/if}

                                    {if $methods[method].static == 1}
                                        static&nbsp;
                                    {/if}

                                    {$methods[method].function_return}&nbsp;

                                    {if $ref == "true"}
                                        &amp;&nbsp;
                                    {/if}

                                    <strong>{$methods[method].function_name}</strong>
                                {/strip}</td>
                                <td nowrap="nowrap" width="100%">{strip}
                                    {build_argument_list args=$methods[method].ifunction_call.params style="vertical"}
                                {/strip}</td>
                            </tr>
                        </table>

                        <p>{$methods[method].sdesc}</p>

                        {if $methods[method].desc}
                            {$methods[method].desc}
                        {/if}

                        {include file="_tags.tpl" tags=$methods[method].tags}
                        <p/>
                    </td>
                </tr>
            {/if}
        {/section}
    </table>
{/if}