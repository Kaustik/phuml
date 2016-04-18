<?php

use Phuml\Generator\PhpProperty;

class plPhpInterface extends PhpProperty
{
    private $properties;

    public function __construct(
        $name,
        $functions = array(),
        $extends = null,
        $namespace = ''
    ) {
        $this->properties = array(
            'name' => $name,
            'functions' => $functions,
            'extends' => $extends,
            'namespace' => $namespace,
        );
        $this->name = $this->getFormattedName();
    }
}
