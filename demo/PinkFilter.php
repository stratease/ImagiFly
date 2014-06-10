<?php

use stratease\ImageBuilder\Filter\BaseFilter;
use Intervention\Image\Image;
class PinkFilter extends BaseFilter {

    public static function getFilterMask()
    {
        return 'pink';
    }

    public function filter($add = false)
    {
        // Adds a varying level of pink!!! WOOHOO!!
        $this->canvas->colorize(rand(50, 80), 0, rand(10, 50));

        // yah baby, unicorns make everything better!
        if($add === 'unicorn') {
            $unicorn = Image::make(__DIR__.'/images/unicorn.png');
            // make smaller...
            $unicorn->resize(75, 75, true);
            // randomize location it's placed...
            $this->canvas->insert($unicorn, rand(0, 100), 0);
        }

        return $this->canvas;
    }
}