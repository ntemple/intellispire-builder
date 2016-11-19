<? defined('KOOWA') or die; ?>
<ul>
<? foreach(@${$models} as ${$model}) : ?>
    <li>
      <a href="<?= @route('view={$model}&id='.${$model}->id); ?>"><?=${$model}->id?>.</a>
{foreach from=$fields item=field}
      <?=@${$model}->{$field} ?>
{/foreach}   
    </li>
<? endforeach; ?>
</ul>

