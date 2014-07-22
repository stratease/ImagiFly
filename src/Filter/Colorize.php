<?php

namespace stratease\ImagiFly\Filter;
use stratease\ImagiFly\Filter\BaseFilter;
use Intervention\Image\ImageManagerStatic;

class Colorize extends BaseFilter {
    public static function getFilterMask() {
        return "/^colorize/";
    }

    /**
     * A number between -100 through 100. Negatives will subtract the given color from the canvas.
     * @param int $r
     * @param int $g
     * @param int $b
     * @return mixed
     */
    public function filter($r, $g, $b)
    {
        $this->canvas->colorize($r, $g, $b);

        return $this->canvas;
    }

    public function writesBaseImageToCanvas()
    {
        return false;
    }
} 