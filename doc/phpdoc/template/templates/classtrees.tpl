{include file="_header.tpl"}

<h1>{$title}</h1>

{section name=tree loop=$classtrees}
    <h4>Root class {$classtrees[tree].class}</h4>
    {$classtrees[tree].class_tree}
{/section}

{include file="_footer.tpl"}