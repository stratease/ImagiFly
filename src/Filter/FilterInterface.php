<?php

namespace stratease\ImageBuilder\Filter;


interface FilterInterface {
    public function __construct(Intervention\Image\Image $canvas, Intervention\Image\Image $baseImage);
    public function filter();
} 