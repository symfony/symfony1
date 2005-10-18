{foreach key=subpackage item=files from=$classleftindex}
    <ul>
        {if $subpackage != ""}<li>symfony.{$subpackage}</li>{/if}
        {section name=files loop=$files}
            <li>{if $files[files].link != ''}<a href="{$files[files].link}">{/if}{$files[files].title}{if $files[files].link != ''}</a>{/if}</li>
        {/section}
    </ul>
{/foreach}
