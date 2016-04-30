<?php

use Phuml\Generator\PhpProperty;

class plPhpInterface extends PhpProperty
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var null|string
     */
    public $extends;
    /**
     * @var plPhpFunction[]
     */
    public $functions;
    /**
     * @var string
     */
    public $namespace;

    /**
     * @param string $name
     * @param array $functions
     * @param string $extends
     * @param string $namespace
     */
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
        $this->extends = $extends;
        $this->functions = $functions;
        $this->name = $name;
        $this->namespace = $namespace;
        $this->name = $this->getFormattedName();
    }
}
