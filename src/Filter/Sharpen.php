<?php

namespace stratease\ImagiFly\Filter;
use stratease\ImagiFly\Filter\BaseFilter;
use Intervention\Image\ImageManagerStatic;

class Sharpen extends BaseFilter {
    public static function getFilterMask() {
        return "/^sharpen/";
    }


    /**
     * @param $amount
     * @return mixed
     */
    public function filter($amount)
    {
        $this->canvas->sharpen($amount);

        return $this->canvas;
    }

    public function writesBaseImageToCanvas()
    {
        return false;
    }
} 