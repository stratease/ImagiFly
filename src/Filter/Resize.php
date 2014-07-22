<?php

namespace stratease\ImagiFly\Filter;
use stratease\ImagiFly\Filter\BaseFilter;

class Resize extends BaseFilter {
    public static function getFilterMask() {
        return "/(\d+)[x|X](\d+)/";
    }
    public function filter($width, $height)
    {
        // Resize both the base image.. this enforces all future filters from the base image will reflect the resize done
        $this->baseImage->resize($width, $height, function($constraint) {
            $constraint->aspectRatio();
        });
        // .. and canvas
        $c = $this->canvas->resize($width, $height, function($constraint) {
            $constraint->aspectRatio();
        });

        return $c;
    }

    /**
     * In our case, the filter is the args
     * @param array $filter
     * @return array
     */
    public function buildArgs(array $filter)
    {
        if(preg_match(self::getFilterMask(), $filter['filter'], $matches)) {

            return [$matches[1], $matches[2]];
        }

        return [];
    }

    public function writesBaseImageToCanvas()
    {
        return false;
    }
} 