<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.2//EN"
"http://www.openmobilealliance.org/tech/DTD/xhtml-mobile12.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
	<title><?php echo $site->getMetaTitle(); ?></title>
	<meta name="description"
		content="<?php echo $site->getMetaDescription(); ?>" />
	<meta http-equiv="Cache-Control" content="max-age=1000" />
	<meta name="viewport"
		content="width=device-width, minimum-scale=1.0, maximum-scale=2.0, user-scalable=no" />
	<meta name="HandheldFriendly" content="true" />
	<meta name="MobileOptimized" content="width" />
	<link
		href="<?php echo MS_ASSETS_URI ?>css/style<?php echo $site->get('stylenumber', 0); ?>.css"
		rel="stylesheet" type="text/css" />
	<?php if ($site->get('icons') == "on"): ?>
	<link href="<?php echo MS_ASSETS_URI ?>css/icons.css" rel="stylesheet"
		type="text/css" />
	<?php endif; ?>
	<script
		src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"
		type="text/javascript"></script>
	<?php
	if ($site->analyticsid) {
		?>
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', '<?php echo $site->analyticsid; ?>']);
	  _gaq.push(['_trackPageview']);
	
	  (function() {
	    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
		<?php } ?>
</head>
<body>
	<div id="wrapper">
		<div id="header">
		<?php
		echo $site->getLogo();
		?>
			<p class="tagline">
			<?php echo $site->tagline; ?>
			</p>
		</div>

		<?php
		if ($site->bizphone && ($site->clicktocall == 'text')) { ?>
		<div id="clicktocall"
			style="margin: 0 auto 10px auto; font-size: 105%">
			Tap to call: <a href="tel:<?php echo $site->bizphone ?>"><?php echo $site->bizphone ?>
			</a>
		</div>
		<?php
		} else   if ($site->bizphone && ($site->clicktocall == 'image')) { ?>
		<div id="clicktocall">
			<a href="tel:<?php echo $site->bizphone ?>"><img
				src="<?php echo MS_ASSETS_URI ?>images/click-to-call.png"
				width="184" height="41"
				alt="Tap to Call <?php echo $site->bizname ?>" /> </a>
		</div>
		<?php } ?>

		<?php if (!$site->ismenu) { ?>
		<div class="sitecontent">
			<h1>
			<?php echo $site->getMenuItemName()  ?>
				<span class="right"><a href="<?php echo $site->getMobileURL(); ?>">Home</a>
				</span>
			</h1>
			<?php } else { ?>
			<div>
			<?php } ?>
			<?php

            $content =  $site->get('headertext')
                .  $site->getArticle()
                .  $site->getBody()
                .  $site->get('footertext');

            echo $site->runfilters($content);


			?>
			</div>


			<div id="footer">
				&copy;
				<?php echo date("Y") ?>
				<?php echo $site->get('bizname'); ?>
				<span class="right"><a href="#header">Top</a> </span>
				<p class="call">
					<strong><?php echo $site->get('bizname'); ?> </strong><br />
					<?php echo $site->get('address1'); ?>
					<br />
					<?php echo $site->get('address2'); ?>
					<br /> <br />
					<?php
					if ($site->bizphone) {
?>
					Tap to call: <a href="tel:<?php echo $site->bizphone; ?>"><?php echo $site->bizphone; ?>
					</a>
				</p>
				<?php } ?>
			</div>

		</div>
	</div>
	<!-- Wrapper -->
</body>
</html>
