<?php
JToolBarHelper::title( JText::_('Mobile Site Builder' ), 'jomlink.png');
JToolBarHelper::preferences('com_mobilesite', '550');

// error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));
// ini_set('display_errors',1);

$link =  JRoute::_('index.php?option=com_mobilesite', false, -1); // Loads the router under 2.5
$link = route('index.php?option=com_mobilesite', false, -1);

?>
<style type="text/css">
.icon-48-jomlink {
	background-image:
		url(http://media.intellispire.com/images/icon-48-jomlink.png);
}
</style>

<div class="col50">
<fieldset class="adminform"><legend>Mobile Site Builder</legend>

<p>Your Mobile Site Builder allows you to use the power of the Joomla! CMS to build a second, mobile optimized website on top of your current site. 
<p>Here's how you can set up your mobile site for people with Joomla! experience.  Video tutorials are available on the <a href="http://www.intellispire.com">Intellispire Website</a>.
<p><b>Basic Configuration</b></p>
<ol>
<li> Under Menus | Menu Manger, add a new Menu. A good name for this menu would be "mobilesite", but you can use any name Joomla allows.
<li> (optional) Using the Media Manager, upload a mobile optimized logo to use with your mobile site to the /images/ directory.
<li> After creating the menu, click on the Options (or Parameters) button on th upper right corner of this page.
<li> For Menu, select the menu you just created in step 1. 
<li> Fill out the rest of the form and save.  Hovering over the label will give extended help. 
</ol>

<p><b>Adding Content</b></p>
<p>Each page type as at least 3 places where you can add content. You may select an existing Joomla! article, add text above, or below, the main content area using a standard text box. 
If you prefer to use a WYSIWYG editor, you should create your page content now as Joomla! articles. We will link them up in the ext step.
<p> The mobile site builder has a variety of page types, all configured via Joomla!'s built in menu system.
<ol>
<li> Select the menu you created in step 1, above, from Joomla! Menus
<li> Select "New Menu", then choose one of the mobilesite menu item types. While links to other items will work, only mobilesite items are optimized for mobile display.
<li> Fill in the Menu Title and then look at the settings on the right of the page.
<li> Each page type has an optional Article you can link to, as well as Mobile Page Settings and other information that must be filled in for that page type to work. Hover over the label for more details on each field.
<li> Click "Save and Close"
</ol>
<p>Repeat for additional pages for your mobile site.


</fieldset>
</div>

<div class="col50">
<fieldset class="adminform"><legend>Accessing your Site: QR Code</legend>

<img src="https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=<?php echo urlencode(utf8_encode($link)) ?>">
<p>&nbsp;</p>
<p>Save and print this QR Code to give people access to your Mobile Site.  Let people find you by adding it it to all your marketing: advertising, business cards, posters ... even T-Shirts and cars.
<p>Direct link: <a href="<?php echo $link?>" target="_blank"><?php echo $link?></a></p>

</fieldset>
</div>


<div class="col50">
<fieldset class="adminform"><legend>Compatibility</legend>
<p>The Site Builder should be compatible with all versions of Joomla 1.5, 2.5 and 3.0,
on any server running Joomla!. However, we only provide direct support
to Joomla 1.5.x, 2.5.x and 3.0 on Linux servers with Cpanel or
similar hosting. We're sorry, we cannot support Microsoft Windows hosted
sites at this time. 
</p>
<p>For support, please contact at our helpdesk:<br>
<a href="http://support.intellispire.com">http://support.intellispire.com</a> 


</p>
</fieldset>
</div>

</div>

<?php
        function route($url) {

                $router = JRouter::getInstance('administrator');
                $uri = $router->build($url);
                $url = $uri->toString(array('path', 'query', 'fragment'));

                if(version_compare(JVERSION,'1.6.0','ge')) {
                        $current = JURI::current();
                        $uri->parse($current);
                        $url = $uri->getScheme() . '://' . $uri->getHost() . $url;
                } else {
                        $base = JURI::base(false);
                        $url = $base . $url;
                }
                $url = str_replace('/administrator/', '/', $url);
                return $url;
        }

