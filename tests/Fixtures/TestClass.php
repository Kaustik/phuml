<?php

namespace Test\Fixtures;

use Test\Fixtures\Child\TestChildClass;
use Test\Fixtures\Child\TestChildClassWithoutUse;
use Test\Fixtures\Interfaces\TestInterface;

class TestClass implements TestInterface
{
    /**
     * @param TestChildClass $testChildClass
     *
     * @return TestChildClassWithoutUse
     */
    public function testFunction(TestChildClass $testChildClass)
    {
        return new TestChildClassWithoutUse();
    }

    public function testArrayParam(array $testParam)
    {
    }
}
