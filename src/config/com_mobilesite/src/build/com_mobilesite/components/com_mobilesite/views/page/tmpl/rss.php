Content
<?php
$items = $site->rss($site->get('rsslink'), $site->get('numitems'), $site->get('cachetime'));
/*<h4><a href="<?php echo $item->get_permalink(); ?>"><?php echo $item->get_title(); ?></a> <?php echo $item->get_date('j M Y'); ?></h4>*/
?>
    	<div id="rss">
			<?php foreach($items as $item): ?>
				<div class=rss_item" style="padding:0 5px;">
					<h4><?php echo $item['title'] ?> <?php echo $item['date'] ?></h4>
					<?php echo $item['content'] ?>
				</div>
			<?php endforeach; ?>		
	   </div>
