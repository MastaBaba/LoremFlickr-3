<?php

//In case you want to have error reporting turned on

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

// Edit the lines below to add your Flickr API Key and Secret
// You can get these from http://www.flickr.com/services/apps/create/apply/

$flickrApiKey = '';
$flickrApiSecret = '';

//Config values.
//Since early 2025, Flickr has been enforcing API usage limits. The documented limit is 3600 requests per hour.

$site["URL"] = "https://my-website.com"; //Website URL
$site["folder"] = "/lf3/"; //Folder from website root.
$site["cache"] = "cache/"; //Cache folder
$site["maxFileSize"] = 250000000; //kb, maximum file size to process.
$site["keyLimit"] = 1000; //Maximum number of times per hour an API request with key can be made

//Default image properties
$imageToUse = "assets/img/defaultImage.jpg";
$defItU = $imageToUse;
$licenseToUse = "cc-nc";
$ownerToUse = "MastaBaba";

//Don't edit these.

$site["path"] =  getcwd()."/"; //Path from server root.
$site["cacheSearch"] = $site["cache"]."flickrsearch/"; //Cache folder for searches.
$site["cacheOriginals"] = $site["cache"]."originals/"; //Cache folder for originals.
$site["cacheResized"] = $site["cache"]."resized/"; //Cache folder for resized images.