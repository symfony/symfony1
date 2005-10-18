{if $consts}
    <hr size="1" noshade="noshade"/>
    <a name="constant-details"></a>
    <table class="constant-details" cellspacing="1">
        <tr>
            <th>Constant Details</th>
        </tr>
        {section name=const loop=$consts}
            <tr>
                <td>
                    <a name="{$consts[const].const_dest}"></a>

                    <h3>{$consts[const].const_name}</h3>

                    <p>{$consts[const].sdesc}</p>

                    {if $consts[const].desc}
                        {$consts[const].desc}
                    {/if}

                    <div class="tag-list">
                        <h4 class="tag">Type:</h4>
                        <div class="tag-data">{include file="_get_constant_type.tpl" const=$consts[const].const_value}</div>
                        <h4 class="tag">Value:</h4>
                        <div class="tag-data">{$consts[const].const_value}</div>
                    </div>
                    {include file="_tags.tpl" tags=$consts[const].tags}
                    <p/>
                </td>
            </tr>
        {/section}
    </table>
{/if}