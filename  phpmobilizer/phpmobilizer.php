<?php
require_once ("phpmobilizer.class.php");

$mobile = new phpmobilizer($_GET['url']);
	
setcookie('nomobile','0',0,'/', $mobile->cookieDomain);

$mobile->loadData();


if($mobile->isImage()){
	$mobile->processImage();
}
else{
	$mobile->process();
	$mobile->output();
}

?>
