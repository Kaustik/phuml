<?php

use Phuml\Generator\PhpInterface;
use Test\Fixtures\Child\TestChildClass;
use Test\Fixtures\Child\TestChildClassWithoutUse;

interface GlobalInterface
{
    /**
     * @param PhpInterface[] $rules
     */
    public function otherFunction(array $rules);

    /**
     * @param TestChildClass $testChildClass
     *
     * @return TestChildClassWithoutUse
     */
    public function testFunction(TestChildClass $testChildClass);
}
