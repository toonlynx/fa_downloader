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
$user = ""; //username
$pass = ""; //password
$link = $argv[1];
$fa_mode = $argv[2];
@$max_pages = $argv[3];
$post = false;
$timeout = 20;
$referer = "https://www.furaffinity.net/";
$cookie_set = false;
$mode = false;
if(!isset($max_pages)) 
{
	$max_pages = 5;
}
elseif($max_pages == 0) 
{
	$max_pages = 100;
}
$download_errors = $exists = $saved = 0;
$url = "https://www.furaffinity.net/";
$mode = false;
$cookie_set = true;
$out = curl_run($post, $url, $ua, $timeout, $referer, $cookie_set, $mode, true);
$ua = 'Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)';
if(!is_file("cookie.txt") || strpos($out['html'], "Log in") !== FALSE)
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
				print_msg("Error! Please restart script to relogin!", "red");
				break;
			}
			if(strpos($out['html'], "There are no submissions to list") !== FALSE)
			{
				print_msg("Ok, $i pages given. Downloading.", "blue");	
				break;
			}
		}
		$links = fa_parse($fa_out);
		$fa_user = str_replace(array("http://www.furaffinity.net/gallery/", "/", "https://www.furaffinity.net/gallery/"), "", $link);
		if(!is_dir("out/$fa_user")) mkdir("out/$fa_user", 0777);
		foreach($links as $link)
		{
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
					print_msg("Error image downloading!", "red");
					$download_errors++;
					continue;
				}
				
				file_put_contents("out/$fa_user/$file_name", $out['html']);
				print_msg("Image $file_name saved!", "green");
				$saved++;
			}
			else 
			{
				print_msg("Image $file_name exists!", "blue");
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
	preg_match_all("/\<a\ href\=\"\/view\/([0-9]{4,10})\/\"/", $html, $links);
	return $links[1];
}

function fa_parse_page($html)
{
	$out = array();
	preg_match("/change\ the\ View\"\ alt\=\"(.*?)\"/", $html, $out['name']);
	@$out['name'] = $out['name'][1];
	preg_match("/href=\"\/\/(.*?)\"/", $html, $out['link']);
	$out['link'] = $out['link'][1];
	return $out;	
}

?>
