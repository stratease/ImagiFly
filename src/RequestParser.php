<?php
/**
 * @author Edwin Daniels <stratease@gmail.com>
 */

namespace stratease\ImageBuilder;


use stratease\ImageBuilder\Util\ConfigurableObject;
class RequestParser extends ConfigurableObject implements RequestParserInterface {
    protected $pathPrepend = 'image-builder';
    protected $requestPath = null;
    protected $filterParamSplit = ':';

    public function getFilterParamSplit()
    {
        return $this->filterParamSplit;
    }
    public function setFilterParamSplit($splitChar)
    {
        $this->filterParamSplit = $splitChar;

        return $this;
    }
    public function setPathPrepend($prepend)
    {
        $this->pathPrepend = trim($prepend, '/');

        return $this;
    }

    public function setRequestPath($path)
    {
        $this->requestPath = $path;

        return $this;
    }
    public function getRequestPath()
    {
        return $this->requestPath;
    }
    public function getPathPrepend()
    {
        return $this->pathPrepend;
    }


    public function getRequestedImage()
    {

        // strip out the file path...
        $path = str_replace($this->pathPrepend, '', $this->requestPath);
        $extPos = strpos($path, '.'); // find the file extension;
        $endFilePos = strpos($path, '/', $extPos);
        $path = substr($path, 0, $endFilePos);

        // clean up
        $path = str_replace('//', '/', $path);

        return $path;
    }

    public function getRequestedFilters()
    {
        $filters = [];
        // strip file path off..
        $filterPath = str_replace($this->getRequestedImage(), '', str_replace($this->pathPrepend, '', $this->requestPath));
        // grab filters
        $filterStrings = explode('/', $filterPath);
        foreach($filterStrings as $filterString)
        {
            $fArgs = explode($this->filterParamSplit, $filterString);
            if(!empty($fArgs[0])) {
                $fName = $fArgs[0];
                unset($fArgs[0]);
                $filters[] = ['filter' => $fName,
                                'args' => array_values($fArgs)];
            }
        }

        return $filters;
    }
}

// $url = $_SERVER['REQUEST_URI']; // /img-bldr/natural-cleanse/bottle.jpg/200x200/overlay:3


/*
 $bldr->setBaseImage($fileName);
// size first...
if(isset($width)) {
    $bldr->setBaseSize($width, $height);
}
// then the rest of the filters
foreach($filters as $filter) {
    $bldr = call_user_func_array([$bldr, $filter['name']], $filter['args']);
}
*/