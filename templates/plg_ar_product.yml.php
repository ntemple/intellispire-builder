<?php

$plugins = array('AWeber', 'iContact', 'GetResponse', 'MailChimp', 'CampaignMonitor', 'ConstantContact');

foreach ($plugins as $product) {
    createPluginManifest($product);
}

function createPluginManifest($name, $version = '5.9.0') {

    $p = strtolower($name);

ob_start();
?>
product:
    headers:
        name: <?= $name ?> # name, comment here to force newline in template
        version: <?= $version ?> # version, comment here to force newline in template
    files:
        'install.<?= $p ?>.php':
            composephp:
                - platform/plg_jomlink/src/install.jomlink.php
        '<?= $p ?>.php':
            composephp:
                - platform/plg_jomlink/src/jomlink.php
                - modules/<?= $p ?>/<?= $p ?>.php
        '<?= $p ?>.xml':
            buildpluginxml:
                module: modules/<?= $p ?>/<?= $p ?>.yml
                platform: platform/plg_jomlink/platform.yml
                template: platform/plg_jomlink/src/jomlink.xml
        'manifest.xml':
            buildpluginxml:
                module: modules/<?= $p ?>/<?= $p ?>.yml
                platform: platform/plg_jomlink/platform.yml
                template: platform/plg_jomlink/src/manifest.xml
<?php
$content = ob_get_clean();
  $path = dirname(__DIR__) . "/products/plg_$p";
  @mkdir($path, 0777, true);
  file_put_contents("$path/product.yml", $content);
  print $content;
}
