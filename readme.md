# LoremFlickr

LoremFlickr provides placeholder images for every case, web or print, on almost any subject, in any size.

## How to install

1. Put the files in the location of your choice. 
2. Edit config.php and enter your Flickr API, sserver details, and key usage limits in config.php. Perhaps adjust the cache locations and make sure those folders are server-writable. 

Flickr seems to be enforcing their API usage limits, which are documented to be limited at 3600 per hour. There also appears to be a limit to how many image URLs you can access. But, as these are not requested with an API key, these are not restricted, here. 

Inside the includes folder, dopiaza's DPZFlickr is already included, for accessing the Flickr API.

You might want to add a cronjob to clean the cache of old files.

Depending on where you've put your files, you might need to update the .htaccess file to make sure redirects point to image.php in the right folder.

## How to use

Point your browser to, depending on where you put the files, http://your-website.com/i/width-320/height-340/tag-dog.

You will get a random image matching the parameters you specify in the size you specify. The license is shown in the top left corner. The author is shown in the bottom left corner.

Parameters are added to the query string in the following format, separated by a forward slash: {{parameter}}-{{value}}. All parameters are optional, except 'width' and 'height'. If you do not specify a tag, an image matching the tag 'kitten' will be returned.

The following parameters are accepted:

- width: The width of the image in pixels. 
- height: The height of the image in pixels.
- tag: The tags, or keywords, to match. Seperate with commas.
- mode: How to match the tags. 'all' or 'any'. Default is 'any'.
- lat: The latitude around which to look for images.
- lng: The longitude around which to look for images.
- radius: The radius around the location where to look for images. In kilometers. Default is 5, maximum is 32.
- user: The user ID of the user you want to get results from. This looks like so: 78303790@N00.
- filter: One of 'g', 'p', 'red', 'green', 'blue'. This adds a graphic filter to the image. This might not be supported by your server.
- format: Whether you want an 'image', or 'json', Default is 'image'.

So, you can end up with something like this:

https://your-website.com/i/width-400/height-400/tag-green/user-78303790@N00

If no images can be retrieved, the default image will be returned, which you can set in /assets. Make sure you edit the associated license and author in the config file.

For more details, visit https://loremflickr.com

## Locking

You can have some control of the image that's displayed. Include a lock query string parameter and give it a value that's a positive integer. While the cache is not updated, and sometimes for longer, the same image will be returned.

https://your-website.com/i/width-320/height-240?lock=212

## Multiple images on the same page

Your browser might cache the images when you request the same URL multiple times on the same page. You can resolve this by adding a meaningless querystring to the URL. So, for example...

https://your-website.com/i/width-320/height-240?random=1

https://your-website.com/i/width-320/height-240?random=2

https://your-website.com/i/width-320/height-240?random=3

## Previously

Version 2 is available here: https://github.com/MastaBaba/LoremFlickr-2

Version 1 is available here: https://github.com/MastaBaba/LoremFlickr

## Credits

+ LoremFlickr is maintained by Babak Fakhamzadeh, https://babakfakhamzadeh.com. On Flickr,  https://www.flickr.com/photos/mastababa/
+ The image resize function was originally adapted from here: https://stackoverflow.com/a/747277/1374538	
+ DPZFlickr is maintained by dopiaza: https://github.com/dopiaza/DPZFlickr