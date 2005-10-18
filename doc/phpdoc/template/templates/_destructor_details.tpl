{if $methods}
    {section name=method loop=$methods}
        {if $methods[method].function_name == "__destruct"}
            <hr size="1" noshade="noshade"/>
            <a name="sec-method"></a>
            <table class="method-details" cellspacing="1">
                <tr>
                    <th colspan="3">Destructor Details</th>
                </tr>
                <tr>
                    <td class="method-data">
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

                                    {if $methods[method].abstract == "1"}
                                        abstract&nbsp;
                                    {/if}

                                    {if $methods[method].static == "1"}
                                        static&nbsp;
                                    {/if}

                                    <strong>{$methods[method].function_name}</strong>
                                {/strip}</td>
                                <td nowrap="nowrap">{strip}
                                    {build_argument_list args=$methods[method].ifunction_call.params style="vertical"}
                                {/strip}</td>
                            </tr>
                        </table>

                        <p>{$methods[method].sdesc}</p>

                        {if $methods[method].desc}
                            {$methods[method].desc}
                        {/if}

                        {include file="_tags.tpl" tags=$methods[method].tags}
                    </td>
                </tr>
            </table>
        {/if}
    {/section}
{/if}