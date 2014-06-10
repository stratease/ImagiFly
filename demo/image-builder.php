<?php
/**
 * Point your web directory to the /demo/ folder, or update your server directive accordingly
 * @author Edwin Daniels <stratease@gmail.com>
 *
 */

use stratease\ImageBuilder\Builder;
use stratease\ImageBuilder\RequestParser;
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