{if $children}
    <div class="sub-classes">
        {if $is_interface}
            <h4>Direct Known Sub-interfaces:</h4>
        {else}
            <h4>Direct Known Sub-classes:</h4>
        {/if}

        {assign var="subclasses" value=""}

        {section name=child loop=$children}
            {if !$smarty.section.child.first}
                {append var="subclasses" value=", "}
            {/if}

            {append var="subclasses" value=$children[child].link}
        {/section}

        <div>{$subclasses}</div>
    </div>
{/if}