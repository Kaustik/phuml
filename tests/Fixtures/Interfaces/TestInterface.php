<?php

namespace Test\Fixtures\Interfaces;

interface TestInterface
{
    /**
     * @param TestInterface[]|array $testParam
     */
    public function testArrayParam(array $testParam);
}
