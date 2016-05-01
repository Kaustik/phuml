<?php

namespace Test\Fixtures\Interfaces;

interface TestInterface
{
    /**
     * @param \AbstractTest[] $otherTestParam
     */
    public function __construct(array $otherTestParam);

    /**
     * @param TestInterface[]|array $testParam
     */
    public function testArrayParam(array $testParam);
}
