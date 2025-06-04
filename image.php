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

$vars = setGet();

$requestedWidth = $vars["w"];
$requestedHeight = $vars["h"];
$tags = "";
if (isset($vars["k"])) {
	$tags = $vars["k"];
}
$tagMode = "";
if (isset($vars["m"])) {
	$tagMode = $vars["m"];
}
$filter = "";
if (isset($vars["f"])) {
	$filter = $vars["f"];
}
$user = "";
if (isset($vars["u"])) {
	$user = $vars["u"];
}
$format = "image";
if (isset($vars["format"])) {
	$format = $vars["format"];
}

$values["width"] = $requestedWidth;
$values["height"] = $requestedHeight;
$values["keywords"] = $tags;

if (isset($_GET["lock"])) {
	$fixedId = (int)$_GET["lock"];
}

if ($tags == "") {
	$tags = "kitten";
}
if ($tagMode != "any")
	$tagMode = "all";

$flickr = new Flickr($flickrApiKey, $flickrApiSecret);

$parameters =  array(
	'per_page' 		=> 100,
	'extras' 		=> 'url_sq,url_t,url_s,url_q,url_m,url_n,url_z,url_c,url_l,url_o,path_alias,owner_name,license',
	'tag_mode' 		=> $tagMode,
	'tags' 			=> $tags,
	'license' 		=> "1,2,3,4,5,6,7,8",
	'sort' 			=> 'interestingness-desc',
	'user_id'		=> $user
);

$searchHashed = $site["cacheSearch"].md5($tagMode.$tags.$user).".txt";
if (!file_exists($searchHashed)) {
	
	if (usageWithinLimit()) {
		$response = $flickr->call('flickr.photos.search', $parameters);
	
		file_put_contents($searchHashed, serialize($response), FILE_APPEND | LOCK_EX);
	}
}
else {
	$response = unserialize(file_get_contents('./'.$searchHashed, true));
	
}

if (isset($response)) {
	$photos = $response['photos'];
	$photosCount = count($photos["photo"]);
	
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
}

//Build the thumbnail
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
	$toReturn["radius"] = $values["rad"];
	$toReturn["user"] = $user;
	$toReturn["rawFileUrl"] = $randomPhoto["url_".$sizeToUse];
	
	header('Content-type: application/json');
	header('Cache-Control: max-age='.(7 * 24 * 60 * 60).', public');
	print json_encode($toReturn);
}