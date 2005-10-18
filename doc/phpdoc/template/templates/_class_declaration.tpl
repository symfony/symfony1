<hr size="1" noshade="noshade"/>
<div class="class-declaration">
    {if count($tags) > 0}
        {section name=tag loop=$tags}
            {if $tags[tag].keyword == "abstract"}
                abstract
            {/if}
        {/section}
    {/if}

    {if $is_interface}
        interface
    {else}
        class
    {/if}

    <strong>{$class_name}</strong>

    {if count($class_tree) > 1}
        {section name=tree loop=$class_tree.classes}
            {if $smarty.section.tree.last}
                extends {$class_tree.classes[$smarty.section.tree.index_prev]}
            {/if}
        {/section}
    {/if}

    {if $implements}
        <br/>
        implements
        {foreach item="interface" from=$implements}
            {if !$smarty.foreach.interface.first}
                , {$interface}
            {else}
                {$interface}
            {/if}
        {/foreach}
    {/if}
</div>