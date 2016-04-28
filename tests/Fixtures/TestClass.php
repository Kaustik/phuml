<?php

namespace Test\Fixtures;

use Test\Fixtures\Child\TestChildClass;
use Test\Fixtures\Interfaces\TestInterface;

class TestClass implements TestInterface
{
    public function testFunction(TestChildClass $testChildClass)
    {
    }
}
