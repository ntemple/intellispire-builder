<? defined('KOOWA') or die; ?>
<form action="<?= @route(); ?>" method="get">
  <input type="hidden" name="option" value="com_{$app}" />
  <input type="hidden" name="view" value="{$models}" />
  <fieldset>
  <legend><?= @text('Filters'); ?></legend> 
      <table>
          <tr>
              <td align="left" width="100%">
                  <?= @text('SEARCH'); ?>
                  <input id="search" name="search" value="<?= @$state->search; ?>" />
                  <button onclick="this.form.submit();"><?= @text('SEARCH'); ?></button>
                  <button onclick="document.getElementById('search').value='';this.form.submit();"><?= @text('RESET'); ?></button>
              </td>
          </tr>
      </table>
   </fieldset>
 </form>

<form action="<?= @route()?>" method="post" name="adminForm">
  <input type="hidden" name="id" value="" />
  <input type="hidden" name="action" value="" />
  <input type="hidden" name="boxchecked" value="0" />
  <table class="adminlist"  style="clear: both;">
    <thead>
      <tr>
        <th width="5">
          <?= @text('NUM'); ?>
        </th>
        <th width="20">
          <input type="checkbox" name="toggle" value="" onclick="checkAll(<?= count(@${$models}); ?>);" />          
        </th>
      <th><?= @helper('grid.sort', 'Id', 'id', @$state->direction, @$state->order); ?></th>
      {foreach from=$fields item=field}
        <th><?= @helper('grid.sort', '{$field|capitalize}', '{$field}', @$state->direction, @$state->order); ?></th>
      {/foreach}   
       <th width="30"><?= @helper('grid.sort', 'Enabled', 'enabled', @$state->direction, @$state->order); ?></th>
      </tr>
    </thead>
    
    <tbody>
      <? $i = 0;?>
      <? foreach (@${$models} as ${$model}) : ?>
      <tr>
        <td align="center">
          <?= $i + 1; ?>
        </td>
        <td align="center">
          <?= @helper('grid.id', $i, ${$model}->id); ?>
        </td>
        <td align="left"><a href="<?= @route('view={$model}&id='.${$model}->id); ?>"><?=${$model}->id?></a></td>
              
      {foreach from=$fields item=field}  
        <td align="center"><?= ${$model}->{$field}?></td>
      {/foreach}     
              
        <td align="center"><?= @helper('grid.enable', $assettype->enabled, $i ); ?></td>
      </tr>
      <? ++$i?>
      <? endforeach; ?>

      <? if (!count(@$assettypes)) : ?>
      <tr>
        <td colspan="20" align="center">
          <?= @text('No items found'); ?>
        </td>
      </tr>
      <? endif; ?>
      
    </tbody>    
    <tfoot>
            <tr>
                <td colspan="20">
                     <?= @helper('paginator.pagination', @$total, @$state->offset, @$state->limit) ?>
                </td>
            </tr>
        </tfoot>
  </table>
</form>