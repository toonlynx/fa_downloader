<?php
/*  This is part of FA downloader
 *  (c) Copyright 2013 ToonLynx <toonlynx@gmail.com>
 *
 * This source code is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Public License as published 
 * by the Free Software Foundation; either version 3 of the License,
 * or (at your option) any later version.
 *
 * This source code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * Please refer to the GNU Public License for more details.
 *
 * You should have received a copy of the GNU Public License along with
 * this source code; if not, write to:
 * Free Software Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 *
 */
function curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode, $header=false) 
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_VERBOSE, 0); //if debugging 1
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//	curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:1080");
	curl_setopt($ch, CURLOPT_URL, "$url");
//	curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_HEADER, $header);
	if(!$cookie_set) 
	{
		curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	}
	else 
	{
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookie.txt");
		curl_setopt($ch, CURLOPT_COOKIEJAR, "cookie.txt");
	}
	if($mode == TRUE) 
	{
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	$exec = curl_exec($ch);
	$err = curl_errno($ch);
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
	curl_close($ch);
	$out['http_code'] = $http_code;
	$out['error'] = $err;
	$out['html'] = $exec;
 	return $out;
}
function print_msg($text, $color) 
{
	$c_std="\033[0m";
	$c_red="\033[1m\033[31m";
	$c_green="\033[1m\033[32m";
	$c_blue="\033[36m";
	$c_brown="\033[33m";
	switch($color) 
	{
		case "blue":
			echo $c_green."[".date("H:i:s")."] ".$c_std.$c_blue.$text.$c_std."\n";
		break;
		case "red":
			echo $c_green."[".date("H:i:s")."] ".$c_std.$c_red.$text.$c_std."\n";
		break;
		case "green":
			echo $c_green."[".date("H:i:s")."] ".$c_std.$c_green.$text.$c_std."\n";
		break;
		case "brown":
			echo $c_green."[".date("H:i:s")."] ".$c_std.$c_brown.$text.$c_std."\n";
		break;
	}
	return true;
}
?>
