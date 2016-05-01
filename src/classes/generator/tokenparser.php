<?php

use Phuml\Generator\TypeHint;
use Phuml\Generator\TypeHintList;
use Phuml\Generator\UseStatement;

class plStructureTokenparserGenerator extends plStructureGenerator
{
    private $namespace;
    private $classes;
    private $interfaces;

    private $parserStruct;
    private $lastToken;

    /**
     * @var string
     */
    private $currentFullyQualifiedName;

    private $currentInterfaceName;

    /**
     * @var UseStatement
     */
    private $currentUse;

    public function __construct()
    {
        $this->initGlobalAttributes();
        $this->initParserAttributes();
        $this->initNamespace();
    }

    private function initGlobalAttributes()
    {
        $this->classes = array();
        $this->interfaces = array();
    }

    private function initParserAttributes()
    {
        $this->parserStruct = array(
            'class' => null,
            'interface' => null,
            'function' => null,
            'attributes' => array(),
            'functions' => array(),
            'typehint' => null,
            'returns' => [],
            'params' => array(),
            'implements' => array(),
            'extends' => null,
            'modifier' => 'public',
            'docblock' => null,
            'use' => [],
        );

        $this->lastToken = array();
    }

    private function initNamespace()
    {
        $this->namespace = '\\';
    }

    /**
     * @param array $files
     *
     * @return array
     */
    public function createStructure(array $files)
    {
        $this->initGlobalAttributes();

        foreach ($files as $file) {
            $this->initParserAttributes();
            $this->initNamespace();
            $tokens = token_get_all(file_get_contents($file));

            // Loop through all tokens
            foreach ($tokens as $token) {
                // Split into Simple and complex token
                if (is_array($token) !== true) {
                    $this->currentFullyQualifiedName = '';
                    if ($this->currentInterfaceName) {
                        $this->parserStruct['implements'][] = $this->currentInterfaceName;
                        $this->currentInterfaceName = '';
                    }
                    switch ($token) {
                        case ',':
                            $this->comma();
                            break;

                        case '(':
                            $this->opening_bracket();
                            break;

                        case ')':
                            $this->closing_bracket();
                            break;

                        case '=':
                            $this->equal_sign();
                            break;
                        case ';':
                            if ($this->currentUse instanceof UseStatement) {
                                $this->parserStruct['use'][$this->currentUse->name] = $this->currentUse;
                                $this->currentUse = null;
                            }
                            $this->lastToken = null;
                            break;
                        default:
                            // Ignore everything else
                            $this->lastToken = null;
                    }
                } elseif (is_array($token) === true) {
                    switch ($token[0]) {
                        case T_WHITESPACE:
                            $this->t_whitespace($token);
                            break;

                        case T_FUNCTION:
                            $this->t_function($token);
                            break;

                        case T_VAR:
                            $this->t_var($token);
                            break;

                        case T_VARIABLE:
                            $this->t_variable($token);
                            break;

                        case T_ARRAY:
                            $this->t_array($token);
                            break;

                        case T_CONSTANT_ENCAPSED_STRING:
                            $this->t_constant_encapsed_string($token);
                            break;

                        case T_LNUMBER:
                            $this->t_lnumber($token);
                            break;

                        case T_DNUMBER:
                            $this->t_dnumber($token);
                            break;

                        case T_PAAMAYIM_NEKUDOTAYIM:
                            $this->t_paamayim_neukudotayim($token);
                            break;

                        case T_STRING:
                            $this->t_string($token);
                            break;

                        case T_INTERFACE:
                            $this->t_interface($token);
                            break;

                        case T_CLASS:
                            $this->t_class($token);
                            break;

                        case T_IMPLEMENTS:
                            $this->t_implements($token);
                            break;

                        case T_EXTENDS:
                            $this->t_extends($token);
                            break;

                        case T_PUBLIC:
                            $this->t_public($token);
                            break;

                        case T_PROTECTED:
                            $this->t_protected($token);
                            break;

                        case T_PRIVATE:
                            $this->t_private($token);
                            break;

                        case T_DOC_COMMENT:
                            $this->t_doc_comment($token);
                            break;

                        case T_NAMESPACE:
                            $this->t_namespace($token);
                            break;

                        case T_NS_SEPARATOR:
                            $this->t_string($token);
                            break;

                        case T_USE:
                            $this->t_use($token);
                            break;
                        default:
                            // Ignore everything else
                            $this->lastToken = null;
                            // And reset the docblock
                            $this->parserStruct['docblock'] = null;
                    }
                }
            }
            // One file is completely scanned here

            // Store interface or class in the parser arrays
            $this->storeClassOrInterface();
        }

        // Fix the class and interface connections
        $this->fixObjectConnections();

        // Return the class and interface structure
        return array_merge($this->classes, $this->interfaces);
    }

    private function comma()
    {
        // Reset typehints on each comma
        $this->parserStruct['typehint'] = null;
        if ($this->lastToken == T_NAMESPACE) {
            $this->lastToken = null;
        }
    }

    private function opening_bracket()
    {
        // Ignore opening brackets
    }

    private function closing_bracket()
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // The function declaration has been closed

                // Add the current function
                $this->parserStruct['functions'][] = array(
                    $this->parserStruct['function'],
                    $this->parserStruct['modifier'],
                    $this->parserStruct['params'],
                    $this->parserStruct['docblock'],
                );
                // Reset the last token
                $this->lastToken = null;
                //Reset the modifier state
                $this->parserStruct['modifier'] = 'public';
                // Reset the params array
                $this->parserStruct['params'] = array();
                $this->parserStruct['typehint'] = null;
                // Reset the function name
                $this->parserStruct['function'] = null;
                // Reset the docblock
                $this->parserStruct['docblock'] = null;
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function equal_sign()
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the equal sign
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_whitespace($token)
    {
        // Ignore whitespaces
    }

    private function t_function($token)
    {
        switch ($this->lastToken) {
            case null:
            case T_PUBLIC:
            case T_PROTECTED:
            case T_PRIVATE:
                $this->lastToken = $token[0];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_var($token)
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the T_VAR
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_variable($token)
    {
        switch ($this->lastToken) {
            case T_PUBLIC:
            case T_PROTECTED:
            case T_PRIVATE:
                // A new class attribute
                $this->parserStruct['attributes'][] = array(
                    $token[1],
                    $this->parserStruct['modifier'],
                    $this->parserStruct['docblock'],
                );
                $this->lastToken = null;
                $this->parserStruct['modifier'] = 'public';
                $this->parserStruct['docblock'] = null;
            break;
            case T_FUNCTION:
                // A new function parameter
                $this->parserStruct['params'][] = array(
                    $this->parserStruct['typehint'],
                    $token[1],
                );
            break;
        }
    }

    private function t_array($token)
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the T_ARRAY
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_constant_encapsed_string($token)
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the T_CONSTANT_ENCAPSED_STRING
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_lnumber($token)
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the T_LNUMBER
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_dnumber($token)
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the T_DNUMBER
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_paamayim_neukudotayim($token)
    {
        switch ($this->lastToken) {
            case T_FUNCTION:
                // just ignore the T_PAAMAYIM_NEKUDOTAYIM
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_string($token)
    {
        switch ($this->lastToken) {
            case T_NAMESPACE:
                // Record the document's namespace
                $this->namespace .= $token[1];
                break;
            case T_IMPLEMENTS:
                $this->currentInterfaceName .= $this->getNameFromToken($token);
                break;
            case T_EXTENDS:
                $this->parserStruct['extends'] = $this->getNameFromToken($token);
                break;
            case T_FUNCTION:
                if ($this->parserStruct['function'] === null) {
                    // Function name
                    $this->parserStruct['function'] = $token[1];
                } else {
                    // Type hint
                    $this->parserStruct['typehint'] = $this->getNameFromToken($token);
                }
                break;
            case T_CLASS:
                // Set the class name
                $this->parserStruct['class'] = $token[1];
                // Reset the last token
                $this->lastToken = null;
            break;
            case T_INTERFACE:
                // Set the interface name
                $this->parserStruct['interface'] = $token[1];
                // Reset the last Token
                $this->lastToken = null;
            break;
            case T_USE:
                $this->currentUse->path .= $token[1];
                $this->currentUse->name = $token[1];
                break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_interface($token)
    {
        switch ($this->lastToken) {
            case null:
                // New initial interface token
                // Store the class or interface definition if there is any in the 
                // parser arrays ( There might be more than one class/interface per
                // file )
                $this->storeClassOrInterface();

                // Remember the last token
                $this->lastToken = $token[0];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_class($token)
    {
        switch ($this->lastToken) {
            case null:
                // New initial interface token
                // Store the class or interface definition if there is any in the 
                // parser arrays ( There might be more than one class/interface per
                // file )
                $this->storeClassOrInterface();

                // Remember the last token
                $this->lastToken = $token[0];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_implements($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_extends($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_public($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
                $this->parserStruct['modifier'] = $token[1];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_protected($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
                $this->parserStruct['modifier'] = $token[1];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_private($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
                $this->parserStruct['modifier'] = $token[1];
            break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_doc_comment($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->parserStruct['docblock'] = $token[1];
                break;
            default:
                $this->lastToken = null;
                $this->parserStruct['docblock'] = null;
        }
    }

    private function t_namespace($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
                break;
            default:
                $this->lastToken = null;
        }
    }

    private function t_use($token)
    {
        switch ($this->lastToken) {
            case null:
                $this->lastToken = $token[0];
                $this->currentUse = new UseStatement();
                break;
            default:
                $this->lastToken = null;
        }
    }

    private function storeClassOrInterface()
    {
        // First we need to check if we should store interface data found so far
        if ($this->parserStruct['interface'] !== null) {
            // Init data storage
            $functions = array();

            // Create the data objects
            foreach ($this->parserStruct['functions'] as $function) {
                // Create the needed parameter objects
                $params = array();
                foreach ($function[2] as $param) {
                    $typeHintList = $this->getParameterTypeHintFromDocBlock($function[3], $param[1])->getTypeHints();
                    if (!is_null($param[0])) {
                        $typeHintList[] = new TypeHint($param[0], false);
                    }
                    $params[] = new plPhpFunctionParameter($param[1], new TypeHintList($typeHintList));
                }
                $functions[] = new plPhpFunction(
                    $function[0],
                    $function[1],
                    $params,
                    $this->getReturnTypeHintFromDocBlock($function[3])
                );
            }
            $interface = new plPhpInterface(
                $this->parserStruct['interface'],
                $functions,
                $this->parserStruct['extends'],
                $this->namespace
            );

            // Store in the global interface array
            $this->interfaces[$interface->name] = $interface;
            $this->initParserAttributes();
        }
        // If there is no interface, we maybe need to store a class
        elseif ($this->parserStruct['class'] !== null) {
            // Init data storage
            $functions = array();
            $attributes = array();

            // Create the data objects
            foreach ($this->parserStruct['functions'] as $function) {
                // Create the needed parameter objects
                $params = array();
                foreach ($function[2] as $param) {
                    $typeHintList = $this->getParameterTypeHintFromDocBlock($function[3], $param[1])->getTypeHints();
                    if (!is_null($param[0])) {
                        $typeHintList[] = new TypeHint($param[0], false);
                    }
                    $params[] = new plPhpFunctionParameter($param[1], new TypeHintList($typeHintList));
                }
                $functions[] = new plPhpFunction(
                    $function[0],
                    $function[1],
                    $params,
                    $this->getReturnTypeHintFromDocBlock($function[3])
                );
            }
            foreach ($this->parserStruct['attributes'] as $attribute) {
                $type = null;
                // If there is a docblock try to isolate the attribute type
                if ($attribute[2] !== null) {
                    // Regular expression that extracts types in array annotations
                    $regexp = '/^[\s*]*@var\s+array\(\s*(\w+\s*=>\s*)?(\w+)\s*\).*$/m';
                    if (preg_match($regexp, $attribute[2], $matches)) {
                        $type = $matches[2];
                    } elseif ($return = preg_match('/^[\s*]*@var\s+(\S+).*$/m', $attribute[2], $matches)) {
                        $type = trim($matches[1]);
                    }
                }
                $attributes[] = new plPhpAttribute(
                    $attribute[0],
                    $attribute[1],
                    $type
                );
            }
            $class = new plPhpClass(
                $this->parserStruct['class'],
                $attributes,
                $functions,
                $this->parserStruct['implements'],
                $this->parserStruct['extends'],
                $this->namespace
            );

            $this->classes[$class->name] = $class;
            $this->initParserAttributes();
        }
    }

    private function fixObjectConnections()
    {
        foreach ($this->classes as $class) {
            $implements = array();
            foreach ($class->implements as $key => $impl) {
                $implements[$key] = array_key_exists($impl, $this->interfaces)
                                    ? $this->interfaces[$impl]
                                    : $this->interfaces[$impl] = new plPhpInterface($impl);
            }
            $class->implements = $implements;

            if ($class->extends === null) {
                continue;
            }
            $class->extends = array_key_exists($class->extends, $this->classes)
                              ? $this->classes[$class->extends]
                              : ($this->classes[$class->extends] = new plPhpClass($class->extends));
        }
        foreach ($this->interfaces as $interface) {
            if ($interface->extends === null) {
                continue;
            }
            $interface->extends = array_key_exists($interface->extends, $this->interfaces)
                                 ? $this->interfaces[$interface->extends]
                                 : ($this->interfaces[$interface->extends] = new plPhpInterface($interface->extends));
        }
    }

    /**
     * @param $token
     *
     * @return string
     */
    private function getNameFromToken($token)
    {
        if (isset($this->parserStruct['use'][$token[1]])) {
            $name = $this->parserStruct['use'][$token[1]]->path;

            return $name;
        } else {
            $this->currentFullyQualifiedName .= $token[1];
            $name = $this->currentFullyQualifiedName;

            return $name;
        }
    }

    /**
     * @param string $docBlock
     *
     * @return TypeHintList
     */
    public function getReturnTypeHintFromDocBlock($docBlock)
    {
        $matches = [];
        preg_match('/.*@return *(.*).*/', $docBlock, $matches);
        if (isset($matches[1])) {
            return $this->getTypeHintListFrom($matches[1]);
        }
        return new TypeHintList([]);
    }

    /**
     * @param string $docBlock
     * @param string $param
     * @return TypeHintList
     */
    public function getParameterTypeHintFromDocBlock($docBlock, $param)
    {
        $matches = [];
        $param = str_replace('$', '\$', $param);
        preg_match('/.*@param *(.*) .*'.$param.'.*/', $docBlock, $matches);
        if (isset($matches[1])) {
            return $this->getTypeHintListFrom($matches[1]);
        }
        return new TypeHintList([]);
    }

    /**
     * @param string $typeHint
     * @return TypeHintList
     */
    private function getTypeHintListFrom($typeHint)
    {
        $typeHintList = [];
        $classes = explode('|', $typeHint);
        foreach ($classes as $class) {
            if (substr($class, -2, 2) == '[]') {
                $className = substr($class, 0, -2);
                $isArrayTypeHint = true;
            } else {
                $className = $class;
                $isArrayTypeHint = false;
            }
            if (isset($this->parserStruct['use'][$className])) {
                $className = $this->parserStruct['use'][$class]->path;
            }
            $typeHintList[] = new TypeHint($className, $isArrayTypeHint);
        }
        return new TypeHintList($typeHintList);
    }
}
