<?php

use Phuml\Generator\TypeHintList;

class plPhpFunction
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $modifier;

    /**
     * @var TypeHintList
     */
    public $return;

    /**
     * @var plPhpFunctionParameter[]
     */
    public $params;

    /**
     * @param string                   $name
     * @param string                   $modifier
     * @param plPhpFunctionParameter[] $params
     * @param TypeHintList             $return
     */
    public function __construct($name, $modifier, array $params, TypeHintList $return)
    {
        $this->params = $params;
        $this->return = $return;
        $this->modifier = $modifier;
        $this->name = $name;
    }
}
