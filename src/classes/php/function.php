<?php

use Phuml\Generator\TypeHintList;

class plPhpFunction
{
    private $properties;

    /**
     * @var TypeHintList
     */
    public $return;

    /**
     * @param string $name
     * @param $modifier
     * @param array $params
     * @param TypeHintList $return
     */
    public function __construct($name, $modifier, array $params, TypeHintList $return)
    {
        $this->properties = array(
            'name' => $name,
            'modifier' => $modifier,
            'params' => $params,
        );
        $this->return = $return;
    }

    public function __get($key)
    {
        if (!array_key_exists($key, $this->properties)) {
            throw new plBasePropertyException($key, plBasePropertyException::READ);
        }

        return $this->properties[$key];
    }

    public function __set($key, $val)
    {
        if (!array_key_exists($key, $this->properties)) {
            throw new plBasePropertyException($key, plBasePropertyException::WRITE);
        }
        $this->properties[$key] = $val;
    }
}
