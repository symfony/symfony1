<div class="inheritence-tree">
    <pre>{section name=tree loop=$class_tree.classes}{if $smarty.section.tree.last}<strong>{$class_tree.classes[tree]}</strong>{else}{$class_tree.classes[tree]}{/if}{$class_tree.distance[tree]}{/section}</pre>
</div>