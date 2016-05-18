<?php

namespace Test\Fixtures;

use Test\Fixtures\Child\TestChildClass;
use Test\Fixtures\Child\TestChildClassWithoutUse;
use Test\Fixtures\Interfaces\TestInterface;

class TestClass implements TestInterface, \GlobalInterface
{
    public function __construct($otherTestParam)
    {
    }

    /**
     * @param TestChildClass $testChildClass
     *
     * @return TestChildClassWithoutUse
     */
    public function testFunction(TestChildClass $testChildClass)
    {
        return new TestChildClassWithoutUse('test');
    }

    public function testArrayParam(array $testParam)
    {
    }

    /**
     * @param \Phuml\Generator\PhpInterface[] $rules
     */
    public function otherFunction(array $rules)
    {
        
    }
}
