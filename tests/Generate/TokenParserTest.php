<?php

namespace Test;

class TokenParserTest extends \PHPUnit_Framework_TestCase
{


    public function testInheritAndInterface()
    {
        $fixturePath = __DIR__. '/../Fixtures';
        $tokenparserGenerator = new \plStructureTokenparserGenerator();
        $fileList = [$fixturePath.'/TestClass.php'];
        $result = $tokenparserGenerator->createStructure($fileList);
        
        #var_dump($result);return;
        /** @var \plPhpClass $class */
        $class = $result['\Test\Fixtures\TestClass'];

        /** @var \plPhpInterface $class */
        $interface = $result['\Test\Fixtures\Interfaces\TestInterface'];
        
        $this->assertSame($interface, $class->implements[0]);
    }
}