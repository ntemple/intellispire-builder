<?php 
  # Sabrayla logging functions 
  require_once('Log.php');

/*
 http://www.indelible.org/pear/Log/guide.php#configuring-a-handler

 PEAR_LOG_EMERG 	emerg() 	System is unusable
 PEAR_LOG_ALERT 	alert() 	Immediate action required
 PEAR_LOG_CRIT 	crit() 	Critical conditions
 PEAR_LOG_ERR 	err() 	Error conditions
 PEAR_LOG_WARNING 	warning() 	Warning conditions
 PEAR_LOG_NOTICE 	notice() 	Normal but significant
 PEAR_LOG_INFO 	info() 	Informational
 PEAR_LOG_DEBUG 	debug() 	Debug-level messages

Each priority has a specific mask associated with it. To compute a priority's mask, use the static Log::MASK() method:

$mask = Log::MASK(PEAR_LOG_INFO);

To compute the mask for all priorities up to a certain level, use the Log::UPTO() method:

$mask = Log::UPTO(PEAR_LOG_INFO);

The apply the mask, use the setMask() method:

$logger->setMask($mask);

Masks can be be combined using bitwise operations. To restrict logging to only those events marked as PEAR_LOG_NOTICE or PEAR_LOG_DEBUG:

$mask = Log::MASK(PEAR_LOG_NOTICE) | Log::MASK(PEAR_LOG_DEBUG);
$logger->setMask($mask);

For convenience, two special masks are predefined: PEAR_LOG_NONE and PEAR_LOG_ALL. PEAR_LOG_ALL is especially useful for exluding only specific priorities:

$mask = PEAR_LOG_ALL ^ Log::MASK(PEAR_LOG_NOTICE);
$logger->setMask($mask);

It is also possible to retrieve and modify a Log object's existing mask:

$mask = $logger->getMask() | Log::MASK(PEAR_LOG_INFO);
$logger->setMask($mask);

&Log::singleton($handler, $name, $ident, $conf, $maxLevel);


*/

  # Available methods
  # debug, info, notice, warning, err, crit, emerg

  $GLOBALS['log'] = &Log::singleton('file', 'out.log', 'SABRAYLA', array(), PEAR_LOG_DEBUG);

?>
