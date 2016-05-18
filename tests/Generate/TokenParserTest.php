<?php

namespace Test;

use Phuml\Generator\StructureTokenparserGenerator;

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

        /** @var \Phuml\Generator\PhpClass $class */
        $class = $this->structure['\Test\Fixtures\TestClass'];
        /* @var \Phuml\Generator\PhpInterface $class */
        $interface = $this->structure['\Test\Fixtures\Interfaces\TestInterface'];
        $this->assertSame($interface, $class->implements[0]);
    }

    public function testExtendAndUse()
    {
        $this->givenFiles(['Child/TestChildClass.php']);
        $this->whenParsed();
        /** @var \Phuml\Generator\PhpClass $class */
        $class = $this->structure['\Test\Fixtures\Child\TestChildClass'];
        /** @var \Phuml\Generator\PhpClass $extends */
        $extends = $class->extends;
        /* @var \Phuml\Generator\PhpClass $class */
        $class2 = $this->structure['\Test\Fixtures\TestClass'];

        $this->assertEquals('\Test\Fixtures\TestClass', $extends->name);
        $this->assertSame($class2, $extends);
    }

    public function testExtendAndNoUse()
    {
        $this->givenFiles(['Child/TestChildClassWithoutUse.php']);
        $this->whenParsed();
        /** @var \Phuml\Generator\PhpClass $class */
        $class = $this->structure['\Test\Fixtures\Child\TestChildClassWithoutUse'];
        /** @var \Phuml\Generator\PhpClass $extends */
        $extends = $class->extends;
        /* @var \Phuml\Generator\PhpClass $class */
        $class2 = $this->structure['\Test\Fixtures\TestClass'];

        $this->assertEquals('\Test\Fixtures\TestClass', $extends->name);
        $this->assertSame($class2, $extends);
    }

    public function testFunctionTypeHintWithUse()
    {
        $this->givenFiles(['TestClass.php']);
        $this->whenParsed();

        /** @var \Phuml\Generator\PhpClass $class */
        $class = $this->structure['\Test\Fixtures\TestClass'];
        /** @var \Phuml\Generator\PhpFunction $function */
        $function = $class->functions[1];
        /** @var \Phuml\Generator\PhpFunctionParameter $param */
        $param = $function->params[0];
        $this->assertEquals('\Test\Fixtures\Child\TestChildClass', $param->type);
    }

    public function testInterfaceParamTypeHint()
    {
        $this->givenFiles(['Interfaces/TestInterface.php']);
        $this->whenParsed();

        /** @var \Phuml\Generator\PhpInterface $interface */
        $interface = $this->structure['\Test\Fixtures\Interfaces\TestInterface'];
        $type = $interface->functions[0]->params[0]->type;
        $this->assertEquals('\AbstractTest[]', (string) $type);

        $type = $interface->functions[1]->params[0]->type;
        $this->assertEquals('\Test\Fixtures\Interfaces\TestInterface[]|array', (string) $type);
    }

    public function testGlobalInterface()
    {
        $this->givenFiles(['GlobalInterface.php']);
        $this->whenParsed();

        /** @var \Phuml\Generator\PhpInterface $interface */
        $interface = $this->structure['\GlobalInterface'];
        $this->assertEquals('\GlobalInterface', $interface->name);
        $type = $interface->functions[0]->params[0]->type;
        $this->assertEquals('\Phuml\Generator\PhpInterface[]', (string) $type);
    }

    public function testInheritGlobalInterface()
    {
        $this->givenFiles(['GlobalInterface.php', 'TestClass.php']);
        $this->whenParsed();

        /** @var \Phuml\Generator\PhpClass $class */
        $class = $this->structure['\Test\Fixtures\TestClass'];
        $this->assertEquals('\GlobalInterface', $class->implements[1]->name);
    }

    /**
     * @group dev
     */
    public function testSameNamespace()
    {
        $this->givenFiles(['TestClassInSameNamespace.php', 'TestClass.php']);
        $this->whenParsed();

        /** @var \Phuml\Generator\PhpClass $class */
        $class = $this->structure['\Test\Fixtures\TestClass'];
        $this->assertEquals('testSameNamespace', $class->functions[4]->name);
        $type = $class->functions[4]->params[0]->type;
        $this->assertEquals('\Test\Fixtures\TestClassInSameNamespace[]', (string) $type);
        $type = $class->functions[4]->return;
        $this->assertEquals('\Test\Fixtures\TestClassInSameNamespace', (string) $type);
        $type = $class->functions[5]->params[0]->type;
        $this->assertEquals('\Test\Fixtures\TestClassInSameNamespace', (string) $type);
    }

    private function givenFiles($fileList)
    {
        $this->fileList = array_map(function ($element) {
            return $this->fixturePath.'/'.$element;
        }, $fileList);
    }

    private function whenParsed()
    {
        $tokenparserGenerator = new StructureTokenparserGenerator();
        $this->structure = $tokenparserGenerator->createStructure($this->fileList);
    }
}
