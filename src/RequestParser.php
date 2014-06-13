<?php
/**
 * @author Edwin Daniels <stratease@gmail.com>
 */

namespace stratease\ImagiFly;


use stratease\ImagiFly\Util\ConfigurableObject;
class RequestParser extends ConfigurableObject implements RequestParserInterface
{
    protected $pathPrepend = 'image-builder';
    protected $requestPath = null;
    protected $filterParamSplit = ':';

    /**
     * @return string
     */
    public function getFilterParamSplit()
    {
        return $this->filterParamSplit;
    }

    /**
     * @param string $splitChar The char that separates each filters argument
     * @return $this
     */
    public function setFilterParamSplit($splitChar)
    {
        $this->filterParamSplit = $splitChar;

        return $this;
    }

    /**
     * @param string $prepend  The initial path to denote image parser, typically "image-builder"
     * @return $this
     */
    public function setPathPrepend($prepend)
    {
        $this->pathPrepend = trim($prepend, '/');

        return $this;
    }

    /**
     * @param string $path  The full request URI path
     * @return $this
     */
    public function setRequestPath($path)
    {
        $this->requestPath = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestPath()
    {
        return $this->requestPath;
    }

    /**
     * @return string
     */
    public function getPathPrepend()
    {
        return $this->pathPrepend;
    }


    /**
     * @return mixed|string
     * @throws \Exception
     */
    public function getRequestedImage()
    {
        if($this->requestPath == null) {
            throw new \Exception("The 'requestPath' is undefined!");
        }
        // strip out the file path...
        $path = str_replace($this->pathPrepend, '', $this->requestPath);
        $extPos = strpos($path, '.'); // find the file extension;
        if($endFilePos = strpos($path, '/', $extPos)) {
            $path = substr($path, 0, $endFilePos);
        }

        // clean up
        $path = str_replace('//', '/', $path);

        return $path;
    }

    /**
     * @return array An array of the filters in the format of ['filter' => 'filter-mask', 'args' => ['arg1', 'arg2']]
     * @throws \Exception
     */
    public function getRequestedFilters()
    {
        if($this->requestPath == null) {
            throw new \Exception("The 'requestPath' is undefined!");
        }
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