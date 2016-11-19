<?php

$plugins = array('AWeber', 'iContact', 'GetResponse', 'MailChimp', 'CampaignMonitor', 'ConstantContact');

foreach ($plugins as $product) {
    createPluginManifest($product);
}

function createPluginManifest($name, $version = '1.0.0') {

    $p = strtolower($name);

ob_start();
?>
product:
    headers:
        name: <?= $name ?> # name, comment here to force newline in template
        version: <?= $version ?> # version, comment here to force newline in template
    files:
        '<?= $p ?>.php':
            params2json:
                params: modules/<?= $p ?>/<?= $p ?>.yml
            composephp:
                - platform/newsletter_plugin/src/plugin.php
                - platform/newsletter_plugin/src/settings.php
                - platform/newsletter_plugin/src/platform.php
                - platform/newsletter_plugin/src/updater.php
                - modules/<?= $p ?>/<?= $p ?>.php

<?php
$content = ob_get_clean();
  $path = dirname(__DIR__) . "/products/newsletter_$p";
  @mkdir($path, 0777, true);
  file_put_contents("$path/product.yml", $content);
  print $content;
}
