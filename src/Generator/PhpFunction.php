<?php

namespace Phuml\Generator;

class PhpFunction
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
     * @var PhpFunctionParameter[]
     */
    public $params;

    /**
     * @param string                 $name
     * @param string                 $modifier
     * @param PhpFunctionParameter[] $params
     * @param TypeHintList           $return
     */
    public function __construct($name, $modifier, array $params, TypeHintList $return)
    {
        $this->params = $params;
        $this->return = $return;
        $this->modifier = $modifier;
        $this->name = $name;
    }
}
