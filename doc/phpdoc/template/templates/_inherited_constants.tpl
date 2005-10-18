{if $iconsts}
    {section name=iconst loop=$iconsts}
        <table class="inherited-constants" cellspacing="1">
            <tr>
                <th>Constants Inherited From {$iconsts[iconst].parent_class}</th>
            </tr>
            <tr>
                <td>
                    {assign var="_consts" value=""}

                    {section name=_const loop=$iconsts[iconst].iconsts}
                        {if $_consts != ""}
                            {append var="_consts" value=", "}
                        {/if}

                        {extract_attribute attribute="href"
                                           element=$iconsts[iconst].iconsts[_const].link
                                           var="href" append="no"}

                        {append var="_consts" value="<a href=\""}
                        {append var="_consts" value=$href}
                        {append var="_consts" value="\">"}
                        {append var="_consts" value=$iconsts[iconst].iconsts[_const].name}
                        {append var="_consts" value="</a>"}
                    {/section}

                    {$_consts}
                </td>
            </tr>
        </table>
    {/section}
{/if}