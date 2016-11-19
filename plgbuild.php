#!/usr/bin/php
<?php

date_default_timezone_set('America/Los_Angeles');

define('BASEDIR', __DIR__);
require BASEDIR.'/vendor/autoload.php';

use Symfony\Component\Yaml\Yaml;
use Composer\Json\JsonFile;
/*
$creator = new ProductCreator('com_mailchimp');
$creator->create();
exit();
*/


$plugins = array('aweber', 'icontact', 'getresponse', 'mailchimp', 'campaignmonitor', 'constantcontact');

foreach ($plugins as $product) {
    $creator = new ProductCreator('newsletter_' . $product);
    $creator->create();
}
exit();

// $creator = new ProductCreator('plg_getresponse');
$creator = new ProductCreator('bracketpress_aweber');
$creator->create();
exit();


//$creator = new ProductCreator('bracketpress_aweber');

exit();


/*
if (!isset($argv[1])) {
    print "Need a product to build\n";
    exit;
}
*/


class ProductCreator {

    var $product;
    var $bom;
    var $dir;
    var $params;
    var $zip;

    function __construct($_product) {
        $this->product = $_product;
        $build = file_get_contents(BASEDIR . "/products/{$this->product}/product.yml");
        if( !$build) {
            throw new Exception("Cannot find product build file: $_product");

        }
        $bom = Yaml::parse($build);

        $this->bom = $bom['product'];
        @mkdir(BASEDIR . "/dist/");
        $this->dir = BASEDIR . "/build/{$this->product}";

        // Create necessary variables for product composition
        $data = $this->bom['headers'];
        $data['UNIQUEID'] = 'u' . gen_uuid('') . '_';
        $data['ucname'] = strtoupper($data['name']);
        $data['lcname'] = strtolower($data['name']);
        $data['cname']  = ucfirst($data['lcname']);
        $data['mversion'] = '0';
        $data['date'] = date("Y-m-d");
        $data['pluguser'] = 'plguser' . $data['lcname'];
        $this->params = $data;

    }

    function create() {
        // Build dependencies
        if (isset($this->bom['depends'])) {
            $files = $this->bom['depends'];
            foreach ($files as $file) {
                // protect from one layer of circular dependencies;
                // more than one layer can't be detected and will crash the system.
                if ($file == $this->product) continue;
                print "Building: $file\n";
                $creator = new ProductCreator($file);
                $creator->create();
            }
        }

        `rm -rf $this->dir`;
        @mkdir($this->dir, 0777, true);

        // Make the necessary files
        $files = $this->bom['files'];
        foreach ($files as $file => $builders) {
            if (dirname($file))   @mkdir($this->dir . '/'. dirname($file));

            foreach ($builders as $builder => $build_args) {
                $method = 'build_file_' . $builder;
                if (method_exists($this, $method)) {
                    call_user_func(array($this, $method), "{$this->dir}/$file", $build_args);
                } else {
                    print "WARNING: no builder method $builder\n";
                }
            }
        }

        // Zip the package
        $zipfile = BASEDIR . "/dist/{$this->product}.zip";
        $build = BASEDIR . "/build/";
        $exclude = BASEDIR . '/support/exclude.lst';

        @unlink($zipfile);
        `cd $build; zip -v -r {$zipfile} {$this->product} -x@$exclude`;
    }


    function build_file_params2json($file, $args) {
        foreach ($args as $var => $file) {
            print "==$var==\n";
            $module = Yaml::parse(file_get_contents(BASEDIR . "/$file"));
            $params = $module['module']['plugin']['params'];
            $this->params[$var] = json_encode($params);
        }
    }

    /**
     * @param $file
     * @param $build_args
     *
     * Builds a php file from other php files.
     * Assumes each file begins with <?php (which is stripped),
     * has no closing ?>,
     * and that it is ok to add a newline at the end of file
     */

    function build_file_composephp($file, $build_args) {
        $content = "<?php\n";
        foreach ($build_args as $filename) {
            $lines = file(BASEDIR . '/' . $filename);
            array_shift($lines);
            $content .= join('', $lines) . "\n";
        }

        $content = subst($content, $this->params);
        print "=> $file\n";
        file_put_contents($file, $content);
    }

    function build_file_buildpluginxml($file, $args) {
        $module_file   = $args['module'];
        $platform_file = $args['platform'];
        $template_file = $args['template'];

        $module = Yaml::parse(file_get_contents(BASEDIR . "/$module_file"));
        $opts = $module['module']['plugin']['params'];

        $platform = Yaml::parse(file_get_contents(BASEDIR . "/$platform_file"));
        $platform_opts = $platform['platform']['plugin']['params'];
        foreach ($platform_opts as $opt) {
            $opts[] = $opt;
        }

        $this->params['FieldUIplugin'] = generateUI('field', $opts);
        $this->params['ParamUIplugin'] = generateUI('param', $opts);

        $content = file_get_contents(BASEDIR . "/$template_file");

        $content = subst($content, $this->params);
        file_put_contents($file, $content);
    }

    function build_file_copy($to, $from) {
        $from = BASEDIR . '/' . $from;
        $cmd = "cp -r $from $to";
        print "$cmd\n";
        `$cmd`;
    }

    function build_file_mergecopy($to, $opts) {
        $from = $opts['from'];

        $to = dirname($to); // One off, here
        $from = BASEDIR . '/' . $from;

        $fileset = `cd $from; find . | grep -v svn`;
        $files = explode("\n", $fileset);

        foreach ($files as $file)  {
            $file = trim($file, "\n .");
            if (!$file) continue;

            $src =  $from .  $file;

            $file = str_replace($opts['key'], $this->params['lcname'], $file);
            $dest =  $to .  $file;

            if (is_dir($src)) {
                `mkdir -p $dest`;
                continue;
            }

            $content = file_get_contents($src);
            $content = subst($content, $this->params, $dest);
            file_put_contents($dest, $content);
        }

        print "$from $to";

    }
}

function gen_uuid($sep = '-') {
    return sprintf( "%04x%04x$sep%04x$sep%04x$sep%04x$sep%04x%04x%04x",

        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function generateUI($type, $fields) {

    $f = '';

    foreach ($fields as $field) {
        $item = '';
        $list = false;
        foreach ($field as $n => $v) {
            if ($n == 'type' && $v == 'list') $list = true;
            if ($n == 'type' && $v == 'radio') $list = true;
            if ($n == 'options') continue;
            $item .= " $n=\"$v\"";
        }
        if ($list) {
            $f .= createSelect($type, $field, $item);
        } else {
            $f .= "<$type $item />\n";
        }
    }

    return $f;

}

function createSelect($type, $field, $item) {
    $options = '';

    foreach ($field['options'] as $o ) {
        list($v, $label) = explode(":", $o);
        $options .= "  <option value=\"$v\">$label</option>\n";
    }

    return "<$type $item>\n$options</$type>\n";
}

# ==========================================



$class = $argv[1];
$name = strtolower($class);
$to = 'plg_' . $name;
print "Building $class\n";

$from = 'jomlink';
$fileset = $files = `find ./plg_$from`;


# processMeta($instance, $class, $params);

$params->version = "1.0";
$params->class   = $class;
$params->name    = $name;
$params->date    = '2012-05-22';


$self = dirname(__FILE__);

`rm -rf ./$to`;
`rm -f ./$to.tgz`;
$fileset = `find ./plg_$from | grep -v svn`;
$files = explode("\n", $fileset);
foreach ($files as $file)  {
	$file = trim($file, "\n .");
	$src =  "$self$file";
	$file = str_replace($from, $name, $file);
	$dest = "$self$file";

	if (is_dir($src)) {
           `mkdir -p $dest`;
           continue;
	}

	$tmpl = file_get_contents($src);
	$out = process($file, $tmpl, $params);
	file_put_contents($dest, $out);
}
`tar czf ./$to.tgz ./$to`;
`rm -rf ./$to`;


function process($filename, $content, $ar) {
    $data = (array) $ar;
    $data['name']   = strtolower($data['class']);
    $data['ucname'] = strtoupper($data['name']);
    $data['cname']  = ucfirst($data['name']);
    $data['mversion'] = '0';

    if (isset($data['params'])) {
        $params = $data['params'];
        unset($data['params']);
        $data = array_merge($data, (array) $params);
    }

    $content = subst($content, $data, $filename);
    return $content;
}



function includes($class, $depends) {

	$out = "if (! defined('JOMLINK1')) {\ndefine('JOMLINK1', 'JOMLINK1');\n";
	foreach ($depends as $path) {
		$out .= loadFile("../arapi/classes/" . $path);
	}
	$out .= loadFile("../arapi/classes/$class" . ".php");

	$out .= "\n}\n";

	return $out;
}

function loadFile($path) {

	$file = @file($path);
	// Strip the <?php
	array_shift($file);

	$file = implode("", $file);
	return $file;
}

function processMeta($instance, $class, $params) {

	// Merging master with generic
	$meta = file_get_contents('../arapi/meta/Jomlink1.meta.js');
	$meta2 = json_decode(file_get_contents('../arapi/meta/' . $class . '.meta.js'), true);

	foreach ($meta2 as $section => $opts) {
		print "$section\n";
		$parts = '';
		foreach ($opts as $a) {
			$parts .= json_encode($a) . ",\n";
		}
		$meta = str_replace("{" . $section . "}", $parts, $meta);
	}
	

	
    $override = $class . ".override.meta.js";

	file_put_contents("../arapi/meta/opt-$override", json_format($meta));
	if (file_exists("../arapi/meta/$override")) {
      print "Loading meta xml override.";
	  $meta = file_get_contents("../arapi/meta/$override");
	}
	
	$meta = json_decode($meta, true);

	// Add the correct default form to the system
    $form = htmlentities($instance->getForm());
      foreach ($meta['UIformAdvanced'] as &$item) {
        if ($item['name'] == 'template') {
        	$item['default'] = $form;
        	print "*-> Form updated!\n";
        }
      }
	
	$instance->tweakUI($meta);
      
	
	foreach ($meta as $section => $opts) {
		if (strpos($section, 'UI') === 0) {
			print "* $section\n";
			// this is partof the ui
			$f = "Field" . $section;
			$p = "Param" . $section;
			$params->$f = generateUI('field', $opts);
			$params->$p = generateUI('param', $opts);
		} else {
			$params->$section = $opts; // Just use the variable in the replacement operation.
		}
	}

}

function subst($content, $data, $filename="") {

	foreach ($data as $n => $v) {
		if (!is_scalar($v)) continue;

		$c1 = $content;
		$content = str_replace("/*{".$n ."}*/", $v, $content);
		if ($c1 != $content) print "/* */{} $filename: {$n}\n";

		$c1 = $content;
		$content = str_replace("//{".$n ."}", $v, $content);
		if ($c1 != $content) print "//{} $filename: {$n}\n";

		$c1 = $content;
		$content = str_replace("{".$n ."}", $v, $content);
		if ($c1 != $content) print "{} $filename: {$n}\n";
	}
	return $content;

}


