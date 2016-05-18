<?php

namespace Test;

use Phuml\Generator\StructureTokenparserGenerator;

class DocBlockParserTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnClass()
    {
        $tokenparserGenerator = new StructureTokenparserGenerator();
        $docBlock = '@return Class';
        $typeHintList = $tokenparserGenerator->getReturnTypeHintFromDocBlock($docBlock);
        $typeHint = $typeHintList->getTypeHints()[0];
        $this->assertEquals('Class', $typeHint->getClassName());
        $this->assertFalse($typeHint->isIsArrayOfClass());
        $this->assertEquals('Class', $typeHintList);
    }

    public function testReturnClassArray()
    {
        $tokenparserGenerator = new StructureTokenparserGenerator();
        $docBlock = '@return Class[]';
        $typeHintList = $tokenparserGenerator->getReturnTypeHintFromDocBlock($docBlock);
        $typeHint = $typeHintList->getTypeHints()[0];
        $this->assertEquals('Class', $typeHint->getClassName());
        $this->assertTrue($typeHint->isIsArrayOfClass());
        $this->assertEquals('Class[]', $typeHintList);
    }

    public function testReturnClassArrayAndArray()
    {
        $tokenparserGenerator = new StructureTokenparserGenerator();
        $docBlock = '@return Class[]|array';
        $typeHintList = $tokenparserGenerator->getReturnTypeHintFromDocBlock($docBlock);
        $typeHint = $typeHintList->getTypeHints()[0];
        $this->assertEquals('Class', $typeHint->getClassName());
        $this->assertTrue($typeHint->isIsArrayOfClass());

        $typeHint = $typeHintList->getTypeHints()[1];
        $this->assertEquals('array', $typeHint->getClassName());
        $this->assertFalse($typeHint->isIsArrayOfClass());

        $this->assertEquals('Class[]|array', $typeHintList);
    }

    public function testParamClassArray()
    {
        $tokenparserGenerator = new StructureTokenparserGenerator();
        $docBlock = '@param Class[] $testParam';
        $typeHintList = $tokenparserGenerator->getParameterTypeHintFromDocBlock($docBlock, '$testParam');
        $typeHint = $typeHintList->getTypeHints()[0];
        $this->assertEquals('Class', $typeHint->getClassName());
        $this->assertTrue($typeHint->isIsArrayOfClass());
        $this->assertEquals('Class[]', $typeHintList);
    }

    public function testParamGlobalClassArray()
    {
        $tokenparserGenerator = new StructureTokenparserGenerator();
        $docBlock = '@param \Class[] $testParam';
        $typeHintList = $tokenparserGenerator->getParameterTypeHintFromDocBlock($docBlock, '$testParam');
        $typeHint = $typeHintList->getTypeHints()[0];
        $this->assertEquals('\Class', $typeHint->getClassName());
        $this->assertTrue($typeHint->isIsArrayOfClass());
        $this->assertEquals('\Class[]', $typeHintList);
    }
}
