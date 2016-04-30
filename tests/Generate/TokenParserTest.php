<?php

namespace Test;

class TokenParserTest extends \PHPUnit_Framework_TestCase
{
    private $fixturePath;
    private $structure;

    /**
     * @var string[]
     */
    private $fileList;

    public function setUp()
    {
        $this->fixturePath = __DIR__.'/../Fixtures';
    }

    public function testInheritInterfaceAndUse()
    {
        $this->givenFiles(['TestClass.php', 'Interfaces/TestInterface.php']);
        $this->whenParsed();

        /** @var \plPhpClass $class */
        $class = $this->structure['\Test\Fixtures\TestClass'];
        /* @var \plPhpInterface $class */
        $interface = $this->structure['\Test\Fixtures\Interfaces\TestInterface'];
        $this->assertSame($interface, $class->implements[0]);
    }

    public function testExtendAndUse()
    {
        $this->givenFiles(['Child/TestChildClass.php']);
        $this->whenParsed();
        /** @var \plPhpClass $class */
        $class = $this->structure['\Test\Fixtures\Child\TestChildClass'];
        /** @var \plPhpClass $extends */
        $extends = $class->extends;
        /* @var \plPhpClass $class */
        $class2 = $this->structure['\Test\Fixtures\TestClass'];

        $this->assertEquals('\Test\Fixtures\TestClass', $extends->name);
        $this->assertSame($class2, $extends);
    }

    public function testExtendAndNoUse()
    {
        $this->givenFiles(['Child/TestChildClassWithoutUse.php']);
        $this->whenParsed();
        /** @var \plPhpClass $class */
        $class = $this->structure['\Test\Fixtures\Child\TestChildClassWithoutUse'];
        /** @var \plPhpClass $extends */
        $extends = $class->extends;
        /* @var \plPhpClass $class */
        $class2 = $this->structure['\Test\Fixtures\TestClass'];

        $this->assertEquals('\Test\Fixtures\TestClass', $extends->name);
        $this->assertSame($class2, $extends);
    }

    public function testFunctionTypeHintWithUse()
    {
        $this->givenFiles(['TestClass.php']);
        $this->whenParsed();

        /** @var \plPhpClass $class */
        $class = $this->structure['\Test\Fixtures\TestClass'];
        /** @var \plPhpFunction $function */
        $function = $class->functions[0];
        /** @var \plPhpFunctionParameter $param */
        $param = $function->params[0];
        $this->assertEquals('\Test\Fixtures\Child\TestChildClass', $param->type);
    }

    public function testInheritParamTypeHint()
    {
        $this->givenFiles(['Interfaces/TestInterface.php']);
        $this->whenParsed();

        /** @var \plPhpInterface $interface */
        $interface = $this->structure['\Test\Fixtures\Interfaces\TestInterface'];
        $type = $interface->functions[0]->params[0]->type;
        $this->assertEquals('\AbstractTest[]', (string)$type);

        $type = $interface->functions[1]->params[0]->type;
        $this->assertEquals('TestInterface[]|array', (string)$type);
    }

    private function givenFiles($fileList)
    {
        $this->fileList = array_map(function ($element) {
            return $this->fixturePath.'/'.$element;
        }, $fileList);
    }

    private function whenParsed()
    {
        $tokenparserGenerator = new \plStructureTokenparserGenerator();
        $this->structure = $tokenparserGenerator->createStructure($this->fileList);
    }
}
