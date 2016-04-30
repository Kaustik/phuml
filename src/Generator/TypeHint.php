<?php

namespace Phuml\Generator;

class TypeHint
{
    /**
     * including namespace or with \ as prefix for global classes
     * string.
     */
    private $className;

    /**
     * @var bool
     */
    private $isArrayOfClass;

    /**
     * TypeHint constructor.
     *
     * @param string $className
     * @param bool   $isArrayOfClass
     */
    public function __construct($className, $isArrayOfClass)
    {
        $this->className = $className;
        $this->isArrayOfClass = $isArrayOfClass;
    }

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return bool
     */
    public function isIsArrayOfClass()
    {
        return $this->isArrayOfClass;
    }
}
