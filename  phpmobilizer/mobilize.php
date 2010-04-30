<?php

require_once "mobiledetect.class.php";
require_once "phpmobilizer.class.php";


$md=new mobiledetect();
$mobile = new phpmobilizer(null);


if($md->isMobile()){
	if($_GET['nomobile']=='1' || $_COOKIE['nomobile'] == '1'){
		setcookie('nomobile', '1', 0, '/', $mobile->cookieDomain );
	}
	else{
		header("location: http://m" . $mobile->cookieDomain );
	}
}
?>
