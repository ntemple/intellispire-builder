<? defined('KOOWA') or die; ?>
<h1>{$model|capitalize}</h1>

{foreach from=$fields item=field}
<p>
  <?=@text('{$field}')?>: <?=@${$model}->{$field} ?>
</p>
{/foreach}   