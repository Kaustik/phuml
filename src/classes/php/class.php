<?php

use Phuml\Generator\PhpProperty;

class plPhpClass extends PhpProperty
{
    public function __construct(
        $name,
        $attributes = array(),
        $functions = array(),
        $implements = array(),
        $extends = null
    ) {
        $this->properties = array(
            'name' => $name,
            'attributes' => $attributes,
            'functions' => $functions,
            'implements' => $implements,
            'extends' => $extends,
        );
    }
}
