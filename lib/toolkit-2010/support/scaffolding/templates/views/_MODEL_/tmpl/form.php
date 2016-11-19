<? defined('KOOWA') or die; ?>
<h1>{$model|capitalize}</h1>

<form action="<?= @route('id='.@${$model}->id) ?>" method="post" class="adminform" name="adminForm">
{foreach from=$fields item=field}
  <dl>
    <dt><label for="{$field}_field"><?= @text('{$model|capitalize} {$field}'); ?></label></dt>
    <dd><input id="{$field}_field" type="text" name="{$field}" value="<?= @${$model}->{$field}; ?>" /></dd>
  </dl>  
{/foreach}     
</form>



 