<?php

namespace stratease\ImagiFly\Filter;
use stratease\ImagiFly\Filter\BaseFilter;
use Intervention\Image\ImageManagerStatic;

class Pixelate extends BaseFilter {

    public static function getFilterMask() {
        return "/^pixelate/";
    }

    /**
     * @param $pixelSize
     * @return mixed
     */
    public function filter($pixelSize)
    {
        $this->canvas->pixelate((int)$pixelSize);

        return $this->canvas;
    }

    public function writesBaseImageToCanvas()
    {
        return false;
    }
} 