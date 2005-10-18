{if $imethods}
    {section name=imethod loop=$imethods}
        {if count($imethods[imethod].imethods) > 1 ||
            ($imethods[imethod].imethods[0].name != "__construct" &&
             $imethods[imethod].imethods[0].name != "__destruct" &&
             $imethods[imethod].imethods[0].abstract != "1")}
            <table class="inherited-methods" cellspacing="1">
                <tr>
                    <th>Methods Inherited From {$imethods[imethod].parent_class}</th>
                </tr>
                <tr>
                    <td>
                        {assign var="_methods" value=""}

                        {section name=_method loop=$imethods[imethod].imethods}
                            {if $imethods[imethod].imethods[_method].name != "__construct" &&
                                $imethods[imethod].imethods[_method].abstract != "1"}
                                {if $_methods != ""}
                                    {append var="_methods" value=", "}
                                {/if}

                                {extract_attribute attribute="href"
                                                   element=$imethods[imethod].imethods[_method].link
                                                   var="href" append="no"}

                                {append var="_methods" value="<a href=\""}
                                {append var="_methods" value=$href}
                                {append var="_methods" value="\">"}
                                {append var="_methods" value=$imethods[imethod].imethods[_method].name}
                                {append var="_methods" value="</a>"}
                            {/if}
                        {/section}

                        {$_methods}
                    </td>
                </tr>
            </table>
        {/if}
    {/section}
{/if}