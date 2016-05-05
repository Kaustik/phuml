<?php

use Phuml\Generator\PhpInterface;

interface GlobalInterface
{
    /**
     * @param \Phuml\Generator\PhpInterface[] $rules
     */
    public function __construct(array $rules);

    /**
     * @return PhpInterface[]
     */
    public function testFunction();
}
