<?php
// Menu
$items = $site->getMenu();
?>
<div class="sitecontenthome zeropad">
    <ul class="menu">
<?php
foreach ($items as $mitem) {
	    $key = $site->getKey();
        print "<li class='{$mitem->icon}'><a href='{$mitem->url}' accesskey='$key'>{$mitem->name}</a></li>\n";
}

?>   <li class="home"><a href="<?php echo $site->getHome(); ?>" accesskey="<?php $site->getKey(); ?>">Visit Our Regular Site</a></li>
    </ul>
</div>
<?php if ($site->facebook || $site->twitter || $site->linkedin) { ?>

<div class="sitecontenthome zeropad">
    <ul class="menu">
<?php if ($site->facebook) { ?>
        <li  class="facebook"><a href="http://m.facebook.com/<?php echo $site->facebook; ?>" accesskey="<?php echo $site->getKey(); ?>">Facebook</a></li>
<?php } ?>
<?php if ($site->twitter) { ?>
        <li  class="twitter"><a href="http://mobile.twitter.com/<?php echo $site->twitter; ?>" accesskey="<?php echo $site->getKey(); ?>">Twitter</a></li>
<?php } ?>
<?php 

// Fix bug where the default was http://
// Here for backwards compatibility with v1.0
// Approximately 5 ppl were affected by this bug
if ($site->linkedin) {

	$linkedin = $site->linkedin;
	if (preg_match('/^http/', $linkedin)) {
		// Legacy Link
		if ($linkedin == 'http://') {
			$linkedin = ''; // No else no URL
		} else {
			// We use the supplied URL
			$linkedin = $site->linkedin;			
		}		
	} else {
	  // This is the default, just a userid
  	  $linkedin = 'http://www.linkedin.com/in/' . $linkedin;
	}
	$site->linkedin = $linkedin;
}

if ($site->linkedin) {
?>
                <li  class="linkedin"><a href="<?php echo $site->linkedin; ?>" accesskey="<?php echo $site->getKey(); ?>">LinkedIn</a></li>
<?php } ?>

    </ul>
</div>
<?php }
