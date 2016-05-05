<?php

namespace Phuml\Generator;

use Phuml\Generator\PhpInterface;

class PhpClass extends PhpProperty
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var PhpClass|string
     */
    public $extends;

    /**
     * @var array
     */
    public $attributes;

    /**
     * @var array
     */
    public $functions;
    /**
     * @var PhpInterface[]|string[]
     */
    public $implements;
    /**
     * @var string
     */
    public $namespace;

    /**
     * @param string $name
     * @param array $attributes
     * @param array $functions
     * @param array $implements
     * @param string $extends
     * @param string $namespace
     */
    public function __construct(
        $name,
        $attributes = [],
        $functions = [],
        $implements = [],
        $extends = null,
        $namespace = ''
    )
    {
        $this->name = $name;
        $this->attributes = $attributes;
        $this->extends = $extends;
        $this->functions = $functions;
        $this->implements = $implements;
        $this->namespace = $namespace;
        $this->name = $this->getFormattedName();
    }
}
