<div class="tag-list">
    {section name=tag loop=$tags}
        {if $tags[tag].keyword != "abstract" &&
            $tags[tag].keyword != "access" &&
            $tags[tag].keyword != "static" &&
	    $tags[tag].keyword != "version"
	}

            <strong>{$tags[tag].keyword|capitalize}:</strong> 
            {$tags[tag].data}<br />
        {/if}
    {/section}
</div>
