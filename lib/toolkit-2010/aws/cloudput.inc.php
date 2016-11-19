<?php

$expires_near  = 'Thu, 15 Apr 2010 20:00:00 GMT';
$expires_far   = 'Sun, 12 Jan 2020 20:00:00 GMT';
$expires_never = '';  

function process($bucket, $bucketbase, $filebase, $expires = 'Sun, 12 Jan 2020 20:00:00 GMT', $usessl = '') { 
 global $files;

$files = array();
getDirectory($filebase);

foreach ($files as $file) {

  $s3file = $file; # str_replace($filebase, '', $file);
  $s3file = preg_replace('#^\.#','', $s3file);

  $ext = strtolower(substr($file, strrpos($file, ".")+1));

  $headers = array();
  if (isset($expires) && $expires) {
    $headers['Expires'] = $expires;
  }

  $mime = '';
  $compress = false;
  switch($ext) {
    case 'txt' : $compress = true; $mime = 'text/plain'; break;
    case 'css' : $compress = true; $mime = 'text/css'; break;
    case 'js'  : $compress = true; $mime = 'application/x-javascript'; break;
    case 'html': $compress = true; $mime = 'text/html'; break;
    case 'xml' : $compress = true; $mime = 'text/xml'; break;
    case 'jpg' : $mime = 'image/jpeg'; break;
    case 'gif' : $mime = 'image/gif'; break;
    case 'png' : $mime = 'image/png'; break;
    case 'tgz' : $mime = 'application/x-tgz'; break;
    case 'wmv' : $mime  = 'video/x-ms-wmv'; break;
    case 'flv' : $mime  = 'video/x-flv'; break;
    case 'zip' : $mime  = 'application/zip'; break;
    case 'swf' : $mime  = 'application/x-shockwave-flash'; break;
    case 'pdf' : $mime  = 'application/pdf'; break;
    default: break; // do nothing;
  }
  if (! $mime) continue; // We don't want this file

  $headers['Content-Type'] = $mime;
  if ($compress) {
    compress($file); 
    $headers['Content-Encoding'] = 'gzip';
  }  
  # upload the file
  print "s3cmd.rb $usessl put '$bucket:$bucketbase$s3file' '$file'  x-amz-acl:public-read";
  foreach ($headers as $h=>$v) {
    print " \"$h:$v\"";
  }
  print "\n";

  if ($compress) {
    uncompress($file);
  }

}

print "\n\n";

} // process

function compress($file) {
   print "gzip '$file'; mv '$file.gz' '$file';\n";
}

function uncompress($file) {
   print "mv '$file' '$file.gz'; gunzip '$file.gz'\n";
}

 
function getDirectory( $path = '.', $level = 0 ){
 global $files;
    $ignore = array( 'cgi-bin', '.', '..' );
    // Directories to ignore when listing output. Many hosts
    // will deny PHP access to the cgi-bin.

    $dh = @opendir( $path );
    // Open the directory to the handle $dh
    
    while( false !== ( $file = readdir( $dh ) ) ){
    // Loop through the directory
    
        if( !in_array( $file, $ignore ) ){
        // Check that this file is not to be ignored
            # $spaces = str_repeat( '&nbsp;', ( $level * 4 ) );
            // Just to add spacing to the list, to better
            // show the directory tree.
            
            if( is_dir( "$path/$file" ) ){
            // Its a directory, so we need to keep reading down...
            
                # echo "<strong>$spaces $file</strong><br />";
                getDirectory( "$path/$file", ($level+1) );
                // Re-call this same function but on a new directory.
                // this is what makes function recursive.
            
            } else {
                $files[] = "$path/$file"; 
                # echo "$spaces $file<br />";
                // Just print out the filename
            
            }
        
        }
    
    }
    
    closedir( $dh );
    // Close the directory handle

} 
 
