<?php
# cpanel4 - Accounting.php.inc              Copyright(c) 1999-2002 John N. Koston
#                                 All rights Reserved.
# nick@cpanel.net              http://cpanel.net         
# 
# This file is governed by the cPanel license


global $cpanelaccterr;

/*
# $b = createacct ('srv05.intellispire.net', 'intellis', $accessHash, true, 'nicktempletest.com','ntest','passw0rd', 'default');
# print_r($b);
# print "$cpanelaccterr\n";

# $a = listaccts('srv05.intellispire.net', 'intellis', $accessHash, true);
# print_r($a);
# print "$cpanelaccterr\n";

# $a = killacct ('srv05.intellispire.net', 'intellis', $accessHash, true, 'ntest');
# print_r($a);
# print "$cpanelaccterr\n";


# $a = add_db('bigbigsales.com', 'bigbig', 't2Zn3HtC', 'testdb'); # comes out as bigbig_testdb
# print_r($a);

# $a = add_subdomain('www.bigbigsales.com', 'bigbig', 't2Zn3HtC', 'test2', 'bigbigsales.com'); 
# print_r($a);

$a = add_domain('www.bigbigsales.com', 'bigbig', 't2Zn3HtC', 'exampletest3.com', 'example', 'example01'); 
# print_r($a);
*/


# Todo: create a method to create a new username and passsword, and a new db associated
function add_db($host, $username, $password, $db) {
  $url = "https://$username:$password@$host:2083/frontend/x/sql/adddb.html?db=$db";
  return _get($url);
#  https://www.bigbigsales.com:2083=db2
}

function add_subdomain($host, $username, $password, $subdomain, $root) {
  $url = "https://$username:$password@$host:2083/frontend/x/subdomain/doadddomain.html?domain=$subdomain&rootdomain=$root";
  return _get($url);
}


function add_domain($host, $username, $password, $domain, $directory, $pwd) {
  $url = "https://$username:$password@$host:2083/frontend/x/addon/doadddomain.html?domain=$domain&user=$directory&pass=$pwd";
  return _get($url);
}





function _get($url) {
#  CURLOPT_USERPWD
# print "$url\n";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);                
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

# curl_setopt($ch, CURLOPT_POST,1);
# curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
# curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);

#  $curlheaders[0] = "Authorization: WHM $authstr";
#                curl_setopt($ch,CURLOPT_HTTPHEADER,$curlheaders);
  $data=curl_exec ($ch);
  curl_close ($ch);
  return $data;

}



function suspend ($host,$user,$accesshash,$usessl,$suspenduser) {
	$result = whmreq("/scripts/remote_suspend?user=${suspenduser}",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }
	return $result;
}
function unsuspend ($host,$user,$accesshash,$usessl,$suspenduser) {
	$result = whmreq("/scripts/remote_unsuspend?user=${suspenduser}",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }
	return $result;
}
function killacct ($host,$user,$accesshash,$usessl,$killuser) {
	$result = whmreq("/scripts/killacct?user=${killuser}&nohtml=1",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }
	return $result;
}
function showversion ($host,$user,$accesshash,$usessl) {
	$result = whmreq("/scripts2/showversion",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }
	return $result;
}
function createacct ($host,$user,$accesshash,$usessl,$acctdomain,$acctuser,$acctpass,$acctplan) {
	$result = whmreq("/scripts/wwwacct?remote=1&nohtml=1&username=${acctuser}&password=${acctpass}&domain=${acctdomain}&plan=${acctplan}",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }
	return $result;
}

function listaccts ($host,$user,$accesshash,$usessl) {
	$result = whmreq("/scripts2/listaccts?nohtml=1&viewall=1",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }

        $page = split("\n",$result);
	foreach ($page as $line) {
		list($acct,$contents) = split("=", $line);
		if ($acct != "") {
			$allc = split(",", $contents);
			$accts[$acct] = $allc;
		}
        }
        return($accts);
}
function listpkgs ($host,$user,$accesshash,$usessl) {
	$result = whmreq("/scripts/remote_listpkg",$host,$user,$accesshash,$usessl);
	if ($cpanelaccterr != "") { return; }


        $page = split("\n",$result);
	foreach ($page as $line) {
		list($pkg,$contents) = split("=", $line);
		if ($pkg != "") {
			$allc = split(",", $contents);
			$pkgs[$pkg] = $allc;
		}
        }
        return($pkgs);
}
function whmreq ($request,$host,$user,$accesshash,$usessl) {

# print "whmreq ($request,$host,$user, accesshash,$usessl)\n";

	$cleanaccesshash = preg_replace("'(\r|\n)'","",$accesshash);
        $authstr = $user . ":" . $cleanaccesshash;
	$cpanelaccterr = "";

# print $authstr;

	if (function_exists("curl_init")) {
# print "curl\n";
		$ch = curl_init();
		if ($usessl) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);                
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
                        $opt = "https://${host}:2087" . $request;	
		} else {
                        $opt = "http://${host}:2086" . $request;
                }
# print "$opt\n";
    	        curl_setopt($ch, CURLOPT_URL, $opt);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	        $curlheaders[0] = "Authorization: WHM $authstr";
                curl_setopt($ch,CURLOPT_HTTPHEADER,$curlheaders);
		$data=curl_exec ($ch);
		curl_close ($ch);
	} elseif (function_exists("socket_create")) {
# print "$socket\n";
		if ($usessl) {
			$cpanelaccterr = "SSL Support requires curl";
			return;
		}
		$service_port = 2086;
		$address = gethostbyname($host);
		$socket = socket_create (AF_INET, SOCK_STREAM, 0);
		if ($socket < 0) {
		        $cpanelaccterr = "socket_create() failed";
			return;
		}
		$result = socket_connect ($socket, $address, $service_port);
		if ($result < 0) {
		        $cpanelaccterr = "socket_connect() failed";
			return;
		}
		$in = "GET $request HTTP/1.0\n";
		socket_write($socket,$in,strlen($in));	
		$in = "Connection: close\n";
		socket_write($socket,$in,strlen($in));	
		$in = "Authorization: WHM $authstr\n\n\n";
		socket_write($socket,$in,strlen($in));	
	
		$inheader = 1;
		while(($buf = socket_read($socket, 512)) != false) {
		  if (!$inheader) {
			  $data .= $buf;
	          }
		  if(preg_match("'\r\n\r\n$'s", $buf)) {
			$inheader = 0;
		  }
		  if(preg_match("'\n\n$'s", $buf)) {
			$inheader = 0;
		  }
		  if(preg_match("'\r\n$'s", $buf)) {
			$inheader = 0;
		  }
		}

	} else {
# print "ERROR!\n";
		$cpanelaccterr = "php not compiled with --enable-sockets OR curl";
		return;
	}
# print "-wmhreq\n";

	return $data;	
}

?>
