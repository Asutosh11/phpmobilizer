<?php

class phpmobilizer {
	public $cookieDomain;
	protected $server;
	protected $url;
	protected $html;
	protected $patterns;
	protected $replacements;
	protected $ext;

	function __construct($url){
		$this->url = $url;
		$this->server = preg_replace('/^m\./', '', $_SERVER['SERVER_NAME']);
		$this->ext = strtolower(end(explode('.', $this->url)));
		$this->patterns = array();
		$this->replacements = array();
		$this->cookieDomain = $this->__getCookieDomain();
	}

	function isImage(){
		switch($this->ext){
		case 'jpg':
		case 'jpeg':
		case 'png':
		case 'gif':
			return true;
			break;
		default: 
			return false;
		}
	}

	function loadData(){
		$fp=fopen($this->_url(), "r");
		while(!feof($fp)){
			$this->html .= fgets($fp);
		}
		fclose($fp);
		
	}

	function process(){

		// header
		$this->_replace('/.*<head>/ism', "<?xml version='1.0' encoding='UTF-8'?><!DOCTYPE html PUBLIC '-//WAPFORUM//DTD XHTML Mobile 1.0//EN' 'http://www.wapforum.org/DTD/xhtml-mobile10.dtd'><html xmlns='http://www.w3.org/1999/xhtml'><head>");

		// title
		$this->_replace('/<head>.*?(<title>.*<\/title>).*?<\/head>/ism', '<head>$1</head>');

		// strip out divs with little content
		$this->_stripContentlessDivs();
		
		// divs/p
		$this->_replace('/<div[^>]*>/ism', '') ;
		$this->_replace('/<\/div>/ism','<br/><br/>');
		$this->_replace('/<p[^>]*>/ism','');
		$this->_replace('/<\/p>/ism', '<br/>') ;
		
		// h tags
		$this->_replace('/<h[1-5][^>]*>(.*?)<\/h[1-5]>/ism', '<br/><b>$1</b><br/><br/>') ;
		
		
		// remove align/height/width/style/rel/id/class tags
		$this->_replace('/\salign=(\'?\"?).*?\\1/ism','');
		$this->_replace('/\sheight=(\'?\"?).*?\\1/ism','');
		$this->_replace('/\swidth=(\'?\"?).*?\\1/ism','');
		$this->_replace('/\sstyle=(\'?\"?).*?\\1/ism','');
		$this->_replace('/\srel=(\'?\"?).*?\\1/ism','');
		$this->_replace('/\sid=(\'?\"?).*?\\1/ism','');
		$this->_replace('/\sclass=(\'?\"?).*?\\1/ism','');
		
		// remove coments
		$this->_replace('/<\!--.*?-->/ism','');
		
		// remove script/style
		$this->_replace('/<script[^>]*>.*?\/script>/ism','');
		$this->_replace('/<style[^>]*>.*?\/style>/ism','');
		
		// multiple \n
		$this->_replace('/\n{2,}/ism','');
		
		// remove multiple <br/>
		$this->_replace('/(<br\s?\/?>){2}/ism','<br/>');
		$this->_replace('/(<br\s?\/?>\s*){3,}/ism','<br/><br/>');
		
		//tables
		$this->_replace('/<table[^>]*>/ism', '');
		$this->_replace('/<\/table>/ism', '<br/>');
		$this->_replace('/<(tr|td|th)[^>]*>/ism', '');
		$this->_replace('/<\/(tr|td|th)[^>]*>/ism', '<br/>');

		// wrap and close
		$this->_wrapAndClose();
		
	}

	function processImage(){
		$max_x=300;
		$max_y=400;
	
		switch ($this->ext) {
			case 'jpg' :
			case 'jpeg': $im  = imagecreatefromjpeg ($this->_url());
						 break;
			case 'gif' : $im  = imagecreatefromgif  ($this->_url());
						 break;
			case 'png' : $im  = imagecreatefrompng  ($this->_url());
						 break;
			default    : $stop = true;
						 break;
		}
		
	  
		if (!isset($stop)) {
			$x = imagesx($im);
			$y = imagesy($im);

			if($max_x > $x && $max_y > $y){
				header("Content-Type: image/png");
				$fp=fopen($this->_url(),'r');
				while($data=fgets($fp)) echo $data;
				fclose($fp);
			}
			else{
				if($max_x > $x) $max_x=$x;
				if($max_y > $y) $max_y=$y;
		
				if (($max_x/$max_y) < ($x/$y)) {
				   $save = imagecreatetruecolor($x/($x/$max_x), $y/($x/$max_x));
				}
				else {
				   $save = imagecreatetruecolor($x/($y/$max_y), $y/($y/$max_y));
				}
				
				imagecopyresized($save, $im, 0, 0, 0, 0, imagesx($save), imagesy($save), $x, $y);
				
				header("Content-Type: image/png");
				imagepng($save);
			}
		}
	}

	function output(){
		header('Content-Type: text/html; charset=utf-8');
		echo mb_convert_encoding($this->html, 'HTML-ENTITIES');
	}
	

//******************************************************************************
//* Private functions
//*

	private function _url(){
		$protocol = (strpos($_SERVER['SERVER_NAME'], 'https:')) === false ? 'http://' : 'https://';
		
		$query = '';
		$qsa = array();
		foreach($_GET as $key => $value){
			if($get != 'url'){
				$qsa[] = $key . '=' . $value;
			}
		}
		
		if(sizeOf($qsa)){
			$query = '?' . implode('&', $qsa);
		} 
		$url = $protocol . $this->server . '/' . $this->url . $query;
		return $url;
	}


	private function _replace($pattern, $replacement, $limit=-1){
		$this->html = preg_replace($pattern, $replacement, $this->html, $limit);
	}
	
	private function _stripContentlessDivs(){
		preg_match_all("/<div[^>]*>.*?<\/div>/ism", $this->html, $divs);

		foreach ($divs[0] as $div){
			$fullLen = strlen($div);
			$strippedDiv = strip_tags($div);
			$strippedLen = strlen($strippedDiv) + 1;
			
			if ($strippedLen / $fullLen * 100 < 10){
				$this->html = str_replace($div, '', $this->html);
			}
		}
		
	}

	private function _wrapAndClose(){
		$this->_replace('/(<body[^>]*>)/ism','$1<div style="font-size:small">');
		
		$fullSiteUrl = $this->_url();
		if(strpos($fullSiteUrl,'?'))
			$fullSiteUrl .= '&nomobile=1';
		else
			$fullSiteUrl .= '?nomobile=1';
			
		$this->_replace('/<\/body>/ism','<br/>This page modified for your mobile device by <a href="http://code.google.com/p/phpmobilizer">phpMobilizer</a> - <a href="' . $fullSiteUrl . '">View Full Site</a><br/><br/></body>');
	}
	
	private function __getCookieDomain(){
		$serverNameArray = explode('.', $_SERVER['SERVER_NAME']);
		$snaCount = count($serverNameArray);
		return '.' . $serverNameArray[$snaCount-2] . '.' . $serverNameArray[$snaCount-1];
	}

}
?>
