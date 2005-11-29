<h1>symfony API</h1>

{sortfilelist}

{foreach key=subpackage item=files from=$classleftindex}
  {if $subpackage != ""}<h2>{$subpackage}</h2>{/if}
  <ul>
  {section name=files loop=$files}
      <li>{if $files[files].link != ''}<a href="{$files[files].link}">{/if}{$files[files].title}{if $files[files].link != ''}</a>{/if}</li>
  {/section}
  </ul>
{/foreach}
