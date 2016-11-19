<?php

class PackageManager {

  var $path;
  var $filename;
  var $ext;
  var $manifest;
  var $xml;
  var $md5;
  var $sd_files; // All files in the package
  var $tmpdir = '/mnt/tmp/';

  function cleanFileName($file) {
    list($fname, $ext) = $this->splitFilename($file);
    $fname  = preg_replace("/[^a-z0-9-_]/", "-", strtolower($fname));
    $ext    = preg_replace("/[^a-z0-9-_]/", "-", strtolower($ext));
    $this->filename = $fname . '.' . $ext;    
    $this->ext = $ext;
    return $this->filename;
  }

  function downloadPackage($url) {
    // parse the filename
    $file = parse_url($url, PHP_URL_PATH);
    $file = basename($file);
    $this->cleanFilename($file);

    $buffer = $this->url_retrieve_curl($url);

    // sanity check, no packages should be less than 1k
    if (! isset($buffer) ) {
      throw new Exception('Could not download package file, buffer is empty.');
    }
    @mkdir($this->tmpdir, 0755, true);
    $target = $this->tmpdir . $this->filename;

    # print "$url => $target\n";

    $f = fopen($target, "w");
    fwrite($f, $buffer);
    fclose($f);

    return $this->getmanifest($target);    
  }

  function getmanifest($src) {
    $found = false;

    if (!file_exists($src)) {
      throw new Exception("File does not exist: $src");
    }

    $this->md5 = md5_file($src);
    $filename = $this->cleanFilename(basename($src));
    # print basename($src) . ": basename($src) == $filename\n";

    $dir = $this->tmpdir . md5($filename . time());
    mkdir($dir, 0755, true);

    $target = "$dir/$filename";
    copy($src, $target);


    $this->archive_extract($dir, $filename);

    $filter = 'xml';
    $this->sd_files = array();

    $this->sd_files = $this->scan_directory_recursively($dir, $filter);

    foreach ($this->sd_files as $path) {
      $mf = file_get_contents($path);
      $sxml = new SimpleXMLElement($mf);
      $xml = $this->simplexml2array($sxml);

      $name = $sxml->getName();
      if ($name == 'install' || $name == 'mosinstall') {
        // root should be "install", or possibly "mosinstall"
        // if (isset($xml['@attributes']['type'])) {
        $this->manifest = $mf;
        $this->xml = $xml;
        $found = true;
        $this->type = $xml['@attributes']['type'];
        break;      
      } 
    }

    # cleanup
    `rm -rf $dir`;
    //$this->destroyDir($dir);               

    return $found;
  }

  function setManifest($mf) {
    $xml = new SimpleXMLElement($mf);
    $xml = $this->simplexml2array($xml);
    $this->manifest = $mf;
    $this->xml = $xml;

    return $xml;
  }



  function getJname() {
    return jid::getJidFromXML($this->xml);
    /*
    $jname = $this->xml['name'];
    $jname = preg_replace('#[/\\\\\. ]+#', '', $jname);
    $jname = str_replace('_', "", $jname);
    $jname = strtolower(str_replace(" ", "", $jname));

    if (!$jname) {
    print_r($this);
    throw new Exception("Could not determine jname. Bad manifest?");
    }

    $type = $this->xml['@attributes']['type'];
    if ($type == 'plugin') {
    # We really should find out more about this plugin.
    $jname = 'plugin_' . $this->xml['@attributes']['group'] . '_'  . $jname;
    } else {
    $jname = $type . '_' . $jname; 
    }  

    return $jname;
    */
  }


  function url_retrieve_curl($url, $timeout = 120) {

    if (! function_exists('curl_version')) {
      throw new Exception('Curl not loaded, cannot retrieve file.');
    }

    $ch = curl_init();
    $timeout = $timeout;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    // Getting binary data
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $contents = curl_exec($ch);

    $err     = curl_errno( $ch );
    $errmsg  = curl_error( $ch );
    $header  = curl_getinfo( $ch );

    # print_r(array($err, $errmsg, $header));

    curl_close($ch);
    return $contents;
  }

  function splitFilename($filename)
  {
    $pos = strrpos($filename, '.');
    if ($pos === false) {
      // dot is not found in the filename
      return array($filename, '');
      // no extension
    } else {
      $basename = substr($filename, 0, $pos);
      $extension = substr($filename, $pos+1);
      return array($basename, $extension);
    }
  }


  function destroyDir($dir, $relative = false)
  {
    $ds = DIRECTORY_SEPARATOR;
    $dir = $relative ? realpath($dir) : $dir;
    $dir = substr($dir, -1) == $ds ? substr($dir, 0, -1) : $dir;
    if (is_dir($dir) && $handle = opendir($dir))
    {
      while ($file = readdir($handle))
      {
        if ($file == '.' || $file == '..')
        {
          continue;
        }
        elseif (is_dir($dir.$ds.$file))
        {
          destroyDir($dir.$ds.$file);
        }
        else
        {
          unlink($dir.$ds.$file);
        }
      }
      closedir($handle);
      rmdir($dir);
      return true;
    }
    else
    {
      return false;
    }
  }


  // ------------ lixlpixel recursive PHP functions -------------
  // scan_directory_recursively( directory to scan, filter )
  // expects path to directory and optional an extension to filter
  // ------------------------------------------------------------
  function scan_directory_recursively($directory, $filter=FALSE, $depth = 0)
  {
    # We really need a breadth-first search. Hope this works!   

    $files = array();
    $dirs  = array();

    if(substr($directory,-1) == '/')
    {
      $directory = substr($directory,0,-1);
    }
    if(!file_exists($directory) || !is_dir($directory))
    {
      return FALSE;
    }

    if (is_readable($directory)) {
      $directory_list = opendir($directory);
      while($file = readdir($directory_list))
        if($file != '.' && $file != '..')
        {
          $path = $directory.'/'.$file; 
          if(is_readable($path)) {
            if (is_file($path)) {
              $extension = end(explode('.',$path));
              if($filter === FALSE || $filter == $extension) $files[] = $path;
            } else if (is_dir($path))
                $dirs[] = $path;
          }
        }
    }
    closedir($directory_list);

    foreach ($dirs as $dir) {
      $morefiles = $this->scan_directory_recursively($dir, $filter, $depth+1);
      $files = array_merge($files, $morefiles);
    }
    # print_r($files);
    return $files;
  }


  function archive_extract($extractdir, $fname)
  {
    // guess type based on $archivename
    $parts = explode('.', $fname);
    $ext = strtolower(array_pop($parts));

    #    print "==== $archivename $extractdir $fname [cd $extractdir; unzip $fname] ==\n";

    if ($ext == 'zip') {
      `cd  $extractdir; /usr/bin/unzip $fname`;
    } else {
      `cd  $extractdir; /bin/tar xzf $fname`;
    }
    return true;
  }

  function simplexml2array($xml) {
    if(is_object($xml)) {
      if (get_class($xml) == 'SimpleXMLElement') {
        $attributes = $xml->attributes();
        foreach($attributes as $k=>$v) {
          if ($v) $a[$k] = (string) $v;
        }
        $x = $xml;
        $xml = get_object_vars($xml);
      }
    }
    if (is_array($xml)) {
      if (count($xml) == 0) return (string) $x; // for CDATA
      foreach($xml as $key=>$value) {
        $r[$key] = $this->simplexml2array($value);
      }
      if (isset($a)) $r['@'] = $a;    // Attributes
      return $r;
    }
    return (string) $xml;
  }

}

class jid {

  static function buildJid($item) {
    if (isset($item->folder)) $folder = $item->folder; else $folder = '';    
    if (! $item->type && isset($item->language)) {
      $type = 'language';
    } else {
      $type = $item->type;
    }
    return self::getJname($type, $item->name, $folder);    
  }

  static function getJid($type, $name, $folder ='') {
    $jname = $name;
    $jname = preg_replace('#[/\\\\\. ]+#', '', $jname);
    $jname = str_replace('_', "", $jname);
    $jname = strtolower(str_replace(" ", "", $jname));

    if ($type == 'plugin') {
      $jname = 'plugin_' . $folder . '_'  . $jname;
    } else {
      $jname = $type . '_' . $jname;
    }
    return strtolower(str_replace( ' ', '_', $jname));
  }  


  static function getJidFromXML($xml) {
    $name = $xml['name'];
    $type = $xml['@attributes']['type'];

    if ($type == 'plugin') {
      $folder = $xml['@attributes']['group'];
    } else {
      $folder = '';
    }

    if ($type == '' && isset($xml['language'])) {
      $type = 'language';
    }

    return self::getJid($type, $name, $folder);
  }

}

