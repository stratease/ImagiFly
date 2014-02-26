<?php

namespace stratease\ImageBuilder\Filter;


interface FilterInterface {
    /**
     * @param \Intervention\Image\Image $canvas - Main image manipulation is done on and then returned
     * @param \Intervention\Image\Image $baseImage - Should be CLONED!
     */
    public function __construct(\Intervention\Image\Image $canvas, \Intervention\Image\Image $baseImage);
}