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
include "func.php";
$user = "123"; //username
$pass = "123"; //password
@$link = strtolower($argv[1]);
@$fa_mode = $argv[2];
@$max_pages = $argv[3];
$post = false;
$timeout = 20;
$referer = "https://www.furaffinity.net/";
$cookie_set = false;
$mode = false;
$i = 0;
$ua = 'Mozilla/5.0 (X11; Linux x86_64; rv:82.0) Gecko/20100101 Firefox/82.0';
if(is_numeric($fa_mode)) {
	$max_pages = $fa_mode;
}
if(@strlen($link) < 2 || !isset($link)) {

	print_msg("Usage: ".basename(__FILE__)." <link to profile> <mode> <max pages count(default 10, 0=10000)>", "green");
	exit(1);
}
if(!isset($max_pages)) 
{
	$max_pages = 10;
}
elseif($max_pages == 0) 
{
	$max_pages = 10000;
}
$download_errors = $exists = $saved = 0;
$url = "https://www.furaffinity.net/";
$mode = false;
$cookie_set = true;
$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode, true);
if(!is_file("cookie.txt") )
// || strpos($out['html'], "Log In") !== FALSE)
{
	$url = "https://www.furaffinity.net/login/";
	$post = "action=login&retard_protection=1&name=$user&pass=$pass&login=Login+to%C2%A0FurAffinity";
	$mode = true;
	$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode, true);
	$mode = false;
	if(strpos($out['html'], "Location: https://fur")  !== FALSE || strpos($out['html'], "logout-link") !== FALSE)
	{
		print_msg("Login successful!", "green");	
	}
	elseif(strpos($out['html'], "in an erroneous username or password"))
	{
		unlink("cookie.txt");
		exit(print_msg("Login failed!", "red"));
	}
	else
	{
		unlink("cookie.txt");
		file_put_contents("pagedump.txt", $out['html']);
		exit(print_msg("Unknown error! Page dump saved!", "red"));
	}
}
else 
{
	print_msg("Using old session...", "green");
	file_put_contents("pagedump.txt", $out['html']);
}
if(!is_dir("out")) mkdir("out", 0777);
$cookie_set = true;
switch($fa_mode) 
{
	case "search":
		print_msg("Mode 'search' selected.", "blue");	
		$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode);
	break;
	
	case "gallery":
	case "default":
		print_msg("Mode 'gallery' selected.", "blue");	
		$fa_out = '';

		for($i=1; $i<$max_pages; $i++)
		{
			$url = $link."/$i/";
			print_msg("Get page $i...", "blue");	
			$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode);
			$fa_out .= $out['html'];
			if(strpos($out['html'], "Log in") !== FALSE)
			{
				unlink("cookie.txt");
				print_msg("Error! Please restart script to relogin! Page dump saved.", "red");
				file_put_contents("pagedump$i.txt", $out['html']);
				break;
			}
			if(strpos($out['html'], "!--button class=\"button standard\" type=\"button\">Next</button-->") !== FALSE || strpos($out['html'], "There are no submissions to list") !== FALSE)
			{
				print_msg("Ok, $i pages given. Downloading.", "blue");	
				break;
			}
			if (strpos($out['html'], "Recent Submissions") !== FALSE)
			{
				print_msg("Error! Index page detected! Page dump saved.", "red");
				file_put_contents("pagedump$i.txt", $out['html']);
				break;			
			}
			if (strpos($out['html'], "could not be found.") !== FALSE)
			{
				print_msg("Error! The username  could not be found. Page dump saved.", "red");
				file_put_contents("pagedump$i.txt", $out['html']);
				break;		
			}
			sleep(1);
		}
		$links = fa_parse($fa_out);
		$fa_user = str_replace(array("/gallery/", "/", "https:","www.furaffinity.net"), "", $link);
		//echo $fa_user;
		if(!is_dir("out/$fa_user") && @strlen($fa_user) > 3) mkdir("out/$fa_user", 0777);
		//echo var_dump($links);
		//die();
		$cnt = count($links);
		$i = 0;
		foreach($links as $link)
		{
			$i++;
			$url = "https://www.furaffinity.net/view/$link/";
			$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode);
			$img = fa_parse_page($out['html']);
			$url = "https://".$img['link'];
			$ext = explode("/", $url);
			$file_name = end($ext);
			//$file_name = str_replace(" ", "_", $img['name']);
			if(!is_file("out/$fa_user/$file_name"))
			{
				$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode);
				if($out['error'] != "0")
				{
					print_msg("Error image $file_name downloading! ($i/$cnt)", "red");
					$download_errors++;
					continue;
				}
				
				file_put_contents("out/$fa_user/$file_name", $out['html']);
				print_msg("Image $file_name saved! ($i/$cnt)", "green");
				$saved++;
			}
			else 
			{
				print_msg("Image $file_name exists! ($i/$cnt)", "blue");
				$exists++;
			}
			
		}
	break;

}
$total = $saved+$download_errors+$exists;
print_msg("Process completed!", "green");
print_msg("SAVED: $saved", "green");
print_msg("EXISTS: $exists", "green");
print_msg("ERRORS: $download_errors", "green");
print_msg("TOTAL: $total", "green");
function fa_parse($html) 
{
	preg_match_all("#\<u\>\<a href\=\"\/view\/([0-9]{4,10})/#", $html, $links);
	return $links[1];
}
// \<u\>\<a href\=\"\/view\/([0-9]{4,10})/\"
function fa_parse_page($html)
{
	$out = array();
	preg_match("/change\ the\ View\"\ alt\=\"(.*?)\"/", $html, $out['name']);
	@$out['name'] = $out['name'][1];
	preg_match("#\<div class\=\"download\"\>\<a href\=\"\/\/(.*?)\"#", $html, $out['link']);
	$out['link'] = $out['link'][1];
	return $out;	
}

?>
