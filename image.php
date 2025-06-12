<?php

$configFile = dirname(__FILE__) . '/config.php';

include $configFile;

spl_autoload_register(function($className) {
	$className = str_replace ('\\', DIRECTORY_SEPARATOR, $className);
	include (dirname(__FILE__) . '/includes/' . $className . '.php');
});

use \DPZ\Flickr;

include "includes/functions.php";

init();

$valids = array("width-", "height-", "tag-", "mode-", "lat-", "lng-", "radius-", "user-", "filter-", "format-");
$values = array(
	"radius"	=> "",
	"lat"		=> "",
	"lng"		=> "",
	"user"		=> ""
);
foreach($_GET as $g) {
	foreach($valids as $valid) {
		$x = explode($valid, $g);

		if (count($x) == 2) {
			$values[substr($valid, 0, strlen($valid) - 1)] = $x[1];
		}
	}
}

if (isset($values["width"]) && isset($values["height"])) {

	$requestedWidth = (int)$values["width"];
	$requestedHeight = (int)$values["height"];

	$tags = "kitten";
	if (isset($values["tag"])) {
		$tags = $values["tag"];
	}

	$tagMode = "all";
	if (isset($values["mode"])) {
		if ($values["mode"] == "any" || $values["mode"] == "all") {
			$tagMode = $values["mode"];
		}
	}

	$filter = "";
	if (isset($values["filter"])) {
		$filter = $values["filter"];
	}

	$format = "image";
	if (isset($values["format"])) {
		if ($values["format"] == "json") {
			$format = "json";
		}
	}

	if (isset($_GET["lock"])) {
		$fixedId = (int)$_GET["lock"];
	}

	
	$values["width"] = $requestedWidth;
	$values["height"] = $requestedHeight;
	$values["keywords"] = $tags;
	
	$flickr = new Flickr($flickrApiKey, $flickrApiSecret);
	
	$parameters =  array(
		'per_page' => 100,
		'extras' => 'url_sq,url_t,url_s,url_q,url_m,url_n,url_z,url_c,url_l,url_k,url_h,url_o,path_alias,owner_name,license',
		'tag_mode' => $tagMode,
		'tags' => $tags,
		'license' => "1,2,3,4,5,6,7,8",
		'sort' => 'interestingness-desc'
	);
	
	if ($values["user"] != "") {
		$parameters["user_id"] = $values["user"];
	}
	if ("".$values["lat"] != "") {
		$parameters["lat"] = $values["lat"];
	}
	if ("".$values["lng"] != "") {
		$parameters["lon"] = $values["lng"];
	}
	if ("".$values["radius"] != "") {
		$parameters["radius"] = $values["radius"];
	}
	else {
		if (isset($parameters["lat"]) && isset($parameters["lon"])) {
			$parameters["radius"] = 32;
		}
	}

	$searchHashed = $site["cacheSearch"].md5(print_r($parameters, true)).".txt";
	if (!file_exists($searchHashed)) {
		if (usageWithinLimit()) {
			$response = $flickr->call('flickr.photos.search', $parameters);
		
			file_put_contents($searchHashed, json_encode($response), FILE_APPEND | LOCK_EX);
		}
		
	}
	else {
		$response = json_decode(file_get_contents($searchHashed, true), true);
	}
	
	$photosCount = 0;
	if (is_array($response)) {
		if (isset($response["photos"])) {
			$photos = $response['photos'];
			$photosCount = count($photos["photo"]);
		}
	}
	
	if ($photosCount > 0) {
		if (isset($fixedId)) {
			$randomId = abs((int)$fixedId) % $photosCount;
		}
		else {
			$randomId = rand(0, $photosCount - 1);
		}

		$randomPhoto = $photos["photo"][$randomId];

		$sizeToUse = getSize($randomPhoto, $requestedWidth, $requestedHeight);
		$newFile = moveToCache($randomPhoto["url_".$sizeToUse]);
	
		if ($newFile) {
			$imageToUse = $newFile;
			$licenseToUse = imageLicense($randomPhoto["license"]);
			$ownerToUse = $randomPhoto["ownername"];
		}
	}
	
	//Build the thumbnail
	if(exif_imagetype($imageToUse) != IMAGETYPE_JPEG){
		$imageToUse = $defItU;
	}
	
	$i = imagecreatefromjpeg($imageToUse);
	$thumbnail = thumbnail_box($imageToUse, $i, $requestedWidth, $requestedHeight);
	imagedestroy($i);
	
	$thumbnail = addFilter($thumbnail, $filter, $licenseToUse, $ownerToUse);
	
	if(is_null($thumbnail)) {
		// image creation or copying failed
		header('HTTP/1.1 500 Internal Server Error');
		exit();
	}
	
	if ($format == "image") {
	
		header("Access-Control-Allow-Origin: *");
		header("Location: ".$site["folder"].$thumbnail);
	
	}
	else {
		$toReturn["file"] = $site["URL"].$site["folder"].$thumbnail;
		$toReturn["license"] = $licenseToUse;
		$toReturn["owner"] = $ownerToUse;
		$toReturn["width"] = $requestedWidth;
		$toReturn["height"] = $requestedHeight;
		$toReturn["filter"] = (is_null($filter))? "": $filter;
		$toReturn["tags"] = $tags;
		$toReturn["tagMode"] = $tagMode;
		$toReturn["lat"] = $values["lat"];
		$toReturn["lng"] = $values["lng"];
		$toReturn["radius"] = $values["radius"];
		$toReturn["user"] = $values["user"];
		
		if (isset($sizeToUse)) {
			$toReturn["rawFileUrl"] = $randomPhoto["url_".$sizeToUse];
		}
		else {
			$toReturn["rawFileUrl"] = $toReturn["file"];
		}
		
		header('Content-type: application/json');
		header('Cache-Control: max-age='.(7 * 24 * 60 * 60).', public');
		print json_encode($toReturn);
	}

}