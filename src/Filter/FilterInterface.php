<?php

namespace stratease\ImageBuilder\Filter;


interface FilterInterface {
    /**
     * @return string Regex of the filter to be parsed from request
     */
    public static function getFilterMask();
    /**
     * Contains an array of ['filter' => 'name', 'args' => ['arg1', 'arg2']]
     * Expects a cleaned up argument array returned
     * @param array $parsedArgs
     * @return array
     */
    public function buildArgs(array $parsedArgs);
    /**
     * @param \Intervention\Image\Image $canvas - Canvas to apply filter to

     */
    public function setCanvas(\Intervention\Image\Image $canvas);

    /**
     * @param \Intervention\Image\Image $baseImage - Might need to be CLONED unless your filter applies to all future filters!
     */
    public function setBaseImage(\Intervention\Image\Image $baseImage);

    /**
     * @return bool Flag to tell whether your filter writes the base image onto the canvas.
     */
    public function writesBaseImageToCanvas();
    /**
     * Filter function that is passed the filter arguments if any exist.
     * @return \Intervention\Image\Image The canvas
     * public function filter();
     */
}