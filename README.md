ImagiFly
=============

PHP Library to process images through any number of filter effects, based on a single source image. Web API Inspired by https://github.com/imsky/holder

Makes use of the [Intervention Image](https://github.com/Intervention/image) library for image processing.

Example
--------

``` html

<img src="/image-builder/path/to/my/file.png/225x225/overlay:2/" >

```
The first portion of the path is the image builder path prepend `/image-builder/` which denotes the remaining portion of the path is to be handled by the parser.

Next is the path to the image `/path/to/my/file.png`. These should typically be a transparent images to allow certain filters to operate appropriately.

And finally there are 2 filters being applied to this image. A special resizing filter `/225x225/` which denotes a 225 pixel width and 225 pixel height, respectively. Followed by `/overlay:2/` an overlay filter, specifying 2 duplicates of the original image to be positioned next to each other.

If we had used an original image of ...

![original file](https://github.com/stratease/ImagiFly/raw/master/demo/images/chipmunks-are-awesome.png)

... after utilizing ImagiFly's filter path, the resulting image would be

![parsed file](https://github.com/stratease/ImagiFly/raw/master/demo/parsed-chipmunk.png)


Setup
-----
Check out our [demo](https://github.com/stratease/ImagiFly/tree/master/demo) to see an example setup.

It's important to note that on the backend we have a server directive to route to our PHP image builder file based on the `/image-builder/...` path.

This will provide a level of overhead for each requested image, and is not recommended for very high traffic sites. We do provide a level of caching to avoid redundant image processing.

Our .htaccess for apache
``` apache

RewriteEngine On
RewriteRule ^image-builder/.*$ image-builder.php [NC,L]

```

This redirects to our backend script that handles the route parsing and image manipulation.

``` php

use stratease\ImagiFly\Builder;
use stratease\ImagiFly\RequestParser;
require_once("../vendor/autoload.php");
require_once("PinkFilter.php");


$builder = new Builder(['baseDirectory' => __DIR__.'/images/', // Path where we store base images. Can be in web directory or any location
    'cache' => false, // Lets disable the cache so we can see our PinkFilter adjustments per request.
    // Our request parser. You can provide your own by implementing the RequestParserInterface
    'requestParser' => new RequestParser(['requestPath' => $_SERVER['REQUEST_URI'],
                                            'pathPrepend' => 'image-builder'])]);

// this is how we register a custom filter, in our case an awesome PinkFilter
$builder->addFilterExtension(PinkFilter::getFilterMask(), new PinkFilter());

$builder->output(); // This will compile and output the image.

// Try this path.. /image-builder/chipmunks-are-awesome.png/225x225/overlay:2/pink:unicorn/
// it'll blow your mind!

```

This implementation also has an example of how to setup a [custom filter](https://github.com/stratease/ImagiFly/raw/master/demo/PinkFilter.php). Refer to the [FilterInterface](https://github.com/stratease/ImagiFly/blob/master/src/Filter/FilterInterface.php) for further documentation.
