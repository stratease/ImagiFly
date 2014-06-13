<?php

namespace stratease\ImagiFly\Util;


class ConfigurableObject {
    public function __construct(array $options = [])
    {
        foreach($options as $param => $val) {
            $pFunc = 'set'.ucwords($param);
            if(is_callable([$this, $pFunc])) {
                $this->$pFunc($val);
            }
            else {
                throw new \InvalidArgumentException(__CLASS__.":".$param." is not a valid property.", E_USER_WARNING);
            }
        }
    }
} 