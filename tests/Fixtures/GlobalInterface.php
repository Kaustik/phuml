<?php

interface GlobalInterface
{
    /**
     * @param \plPhpInterface[] $rules
     */
    public function __construct(array $rules);

    /**
     * @return plPhpInterface[]
     */
    public function testFunction();
}
