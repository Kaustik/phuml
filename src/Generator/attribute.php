<?php

namespace Phuml\Generator;

class plPhpAttribute
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
     * @var string
     */
    public $type;

    /**
     * @param string $name
     * @param string $modifier
     * @param string $type
     */
    public function __construct($name, $modifier, $type)
    {
        $this->modifier = $modifier;
        $this->name = $name;
        $this->type = $type;
    }
}
