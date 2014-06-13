<?php

namespace stratease\ImagiFly;


interface RequestParserInterface {

    /**
     * Returns the requested image path, relative to the base directories
     * @return string
     */
    public function getRequestedImage();

    /**
     * Returns the requested filters. Expects a list of array('filter' => 'filterName', 'args' => ['arg1', 'arg2'], ...
     * @return array
     */
    public function getRequestedFilters();
} 