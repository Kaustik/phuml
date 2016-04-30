<?php

use Phuml\Generator\TypeHintList;

class plPhpFunctionParameter
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var TypeHintList
     */
    public $type;

    /**
     * @param string       $name
     * @param TypeHintList $type
     */
    public function __construct($name, TypeHintList $type)
    {
        $this->name = $name;
        $this->type = $type;
    }
}
