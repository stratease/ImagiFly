<?php

namespace stratease\ImagiFly\Filter;
use stratease\ImagiFly\Filter\BaseFilter;
use Intervention\Image\ImageManagerStatic;

class Sepia extends BaseFilter {

    public static function getFilterMask() {
        return "/^sepia/";
    }

    /**
     * @todo Add an amount arg to tone back the sepia effect?
     * @return mixed
     */
    public function filter()
    {
        // ditch pesky colors
        $this->canvas->greyscale();
        // sepia-ish tone it
        $this->canvas->colorize(25, 11, 0);

        return $this->canvas;
    }

    public function writesBaseImageToCanvas()
    {
        return false;
    }
} 