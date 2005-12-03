{include file="_header.tpl"}

<h1>symfony API</h1>

{sortfilelist}

<ul>
{foreach key=subpackage item=files from=$classleftindex}
  <li>{$subpackage}
  <ul>
  {section name=files loop=$files}
      <li>{if $files[files].link != ''}<a href="{$files[files].link}">{/if}{$files[files].title}{if $files[files].link != ''}</a>{/if}</li>
  {/section}
  </ul></li>
{/foreach}
</ul>

{include file="_footer.tpl"}
