<?php
$link = $site->get('qrtext'); 

?>
<div style="clear:both"></div>
<div align="center">
<img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=<?php echo urlencode(utf8_encode($link)) ?>">
</div>
<div style="clear:both"></div>
