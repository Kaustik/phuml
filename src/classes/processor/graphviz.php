<?php


use Phuml\Generator\PhpClass;

class plGraphvizProcessor extends plProcessor
{
    private $output;

    private $structure;

    public $options;

    /**
     * @var bool[] object name as key
     */
    private $associations;

    public function __construct()
    {
        $this->options = new plGraphvizProcessorOptions();
        $this->associations = [];

        $this->structure = null;
        $this->output = null;
    }

    public function getInputTypes()
    {
        return array(
            'application/phuml-structure',
        );
    }

    public function getOutputType()
    {
        return 'text/dot';
    }

    public function process($input, $type)
    {
        $this->structure = $input;

        $this->output = 'digraph "'.sha1(mt_rand()).'" {'."\n";
        $this->output .= 'splines = true;'."\n";
        $this->output .= 'overlap = false;'."\n";
        $this->output .= 'mindist = 0.6;'."\n";

        foreach ($this->structure as $object) {
            if ($object instanceof PhpClass) {
                $this->output .= $this->getClassDefinition($object);
            } elseif ($object instanceof plPhpInterface) {
                $this->output .= $this->getInterfaceDefinition($object);
            }
        }

        foreach ($this->structure as $object) {
            if ($object instanceof PhpClass) {
                $this->output .= $this->getClassExtendAndImplement($object);
                $this->output .= $this->getClassAssociations($object);
            } elseif ($object instanceof plPhpInterface) {
                $this->output .= $this->getInterfaceAssociations($object);
            }
        }

        $this->output .= '}';

        return $this->output;
    }

    /**
     * @param \Phuml\Generator\PhpClass $class
     *
     * @return string
     */
    private function getClassDefinition(\Phuml\Generator\PhpClass $class)
    {
        $def = '';

        // First we need to create the needed data arrays
        $name = $class->name;

        $attributes = $this->getAttributesModifers($class);

        $functions = $this->getFunctionsModifier($class);

        // Create the node
        $def .= $this->createNode(
            $this->getUniqueId($class),
            array(
                'label' => $this->createClassLabel($name, $attributes, $functions),
                'shape' => 'plaintext',
            )
        );

        return $def;
    }

    /**
     * @param PhpClass $class
     *
     * @return string
     */
    public function getClassAssociations(PhpClass $class)
    {
        $def = $this->getAttributesAssociationDefinition($class);
        $def .= $this->getParametersAssociationDefinition($class);
        $def .= $this->getReturnAssociationDefinition($class);

        return $def;
    }

    public function getInterfaceAssociations(plPhpInterface $interface)
    {
        $def = $this->getParametersAssociationDefinition($interface);
        $def .= $this->getReturnAssociationDefinition($interface);

        return $def;
    }

    private function getInterfaceDefinition($o)
    {
        $def = '';

        // First we need to create the needed data arrays
        $name = $o->name;

        $attributes = [];
        $functions = $this->getFunctionsModifier($o);

        // Create the node
        $def .= $this->createNode(
            $this->getUniqueId($o),
            array(
                'label' => $this->createInterfaceLabel($name, $attributes, $functions),
                'shape' => 'plaintext',
            )
        );

        // Create interface inheritance relation        
        if ($o->extends !== null) {
            // Check if we need an "external" interface node
            if (in_array($o->extends, $this->structure) !== true) {
                $def .= $this->getInterfaceDefinition($o->extends);
            }

            $def .= $this->createNodeRelation(
                $this->getUniqueId($o->extends),
                $this->getUniqueId($o),
                array(
                    'dir' => 'back',
                    'arrowtail' => 'empty',
                    'style' => 'solid',
                )
            );
        }

        return $def;
    }

    private function getModifierRepresentation($modifier)
    {
        return ($modifier === 'public')
               ? ('+')
               : (($modifier === 'protected')
                 ? ('#')
                 : ('-'));
    }

    /**
     * @param plPhpFunctionParameter[] $params
     *
     * @return string
     */
    private function getParamRepresentation($params)
    {
        if (count($params) === 0) {
            return '()';
        }

        $representation = '( ';
        $lineLength = 0;
        for ($i = 0; $i < count($params); ++$i) {
            if (count($params[$i]->type->getTypeHints())) {
                $representation .= $params[$i]->type.' ';
            }

            $representation .= $params[$i]->name;
            if ($i < count($params) - 1) {
                $representation .= ', ';
            }

            if (strlen($representation) - $lineLength > 30) {
                //newline each 2 line
                $lineLength = strlen($representation);
                $representation .= '<BR />';
            }
        }
        $representation .= ' )';

        return $representation;
    }

    private function getUniqueId($object)
    {
        return '"'.spl_object_hash($object).'"';
    }

    private function createNode($name, $options)
    {
        $node = $name.' [';
        foreach ($options as $key => $value) {
            $node .= $key.'='.$value.' ';
        }
        $node .= "]\n";

        return $node;
    }

    private function createNodeRelation($node1, $node2, $options)
    {
        $relation = $node1.' -> '.$node2.' [';
        foreach ($options as $key => $value) {
            $relation .= $key.'='.$value.' ';
        }
        $relation .= "]\n";

        return $relation;
    }

    private function createInterfaceLabel($name, $attributes, $functions)
    {
        // Start the table
        $label = '<<TABLE CELLSPACING="0" BORDER="0" ALIGN="LEFT">';

        // The title
        $label .= '<TR><TD BORDER="'.$this->options->style->interfaceTableBorder.'" ALIGN="CENTER" BGCOLOR="'.$this->options->style->interfaceTitleBackground.'"><FONT COLOR="'.$this->options->style->interfaceTitleColor.'" FACE="'.$this->options->style->interfaceTitleFont.'" POINT-SIZE="'.$this->options->style->interfaceTitleFontsize.'">'.$name.'</FONT></TD></TR>';

        // The attributes block
        $label .= '<TR><TD BORDER="'.$this->options->style->interfaceTableBorder.'" ALIGN="LEFT" BGCOLOR="'.$this->options->style->interfaceAttributesBackground.'">';
        if (count($attributes) === 0) {
            $label .= ' ';
        }
        foreach ($attributes as $attribute) {
            $label .= '<FONT COLOR="'.$this->options->style->interfaceAttributesColor.'" FACE="'.$this->options->style->interfaceAttributesFont.'" POINT-SIZE="'.$this->options->style->interfaceAttributesFontsize.'">'.$attribute.'</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // The function block
        $label .= '<TR><TD BORDER="'.$this->options->style->interfaceTableBorder.'" ALIGN="LEFT" BGCOLOR="'.$this->options->style->interfaceFunctionsBackground.'">';
        if (count($functions) === 0) {
            $label .= ' ';
        }
        foreach ($functions as $function) {
            $label .= '<FONT COLOR="'.$this->options->style->interfaceFunctionsColor.'" FACE="'.$this->options->style->interfaceFunctionsFont.'" POINT-SIZE="'.$this->options->style->interfaceFunctionsFontsize.'">'.$function.'</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // End the table
        $label .= '</TABLE>>';

        return $label;
    }

    private function createClassLabel($name, $attributes, $functions)
    {
        // Start the table
        $label = '<<TABLE CELLSPACING="0" BORDER="0" ALIGN="LEFT">';

        // The title
        $label .= '<TR><TD BORDER="'.$this->options->style->classTableBorder.'" ALIGN="CENTER" BGCOLOR="'.$this->options->style->classTitleBackground.'"><FONT COLOR="'.$this->options->style->classTitleColor.'" FACE="'.$this->options->style->classTitleFont.'" POINT-SIZE="'.$this->options->style->classTitleFontsize.'">'.$name.'</FONT></TD></TR>';

        // The attributes block
        $label .= '<TR><TD BORDER="'.$this->options->style->classTableBorder.'" ALIGN="LEFT" BGCOLOR="'.$this->options->style->classAttributesBackground.'">';
        if (count($attributes) === 0) {
            $label .= ' ';
        }
        foreach ($attributes as $attribute) {
            $label .= '<FONT COLOR="'.$this->options->style->classAttributesColor.'" FACE="'.$this->options->style->classAttributesFont.'" POINT-SIZE="'.$this->options->style->classAttributesFontsize.'">'.$attribute.'</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // The function block
        $label .= '<TR><TD BORDER="'.$this->options->style->classTableBorder.'" ALIGN="LEFT" BGCOLOR="'.$this->options->style->classFunctionsBackground.'">';
        if (count($functions) === 0) {
            $label .= ' ';
        }
        foreach ($functions as $function) {
            $label .= '<FONT COLOR="'.$this->options->style->classFunctionsColor.'" FACE="'.$this->options->style->classFunctionsFont.'" POINT-SIZE="'.$this->options->style->classFunctionsFontsize.'">'.$function.'</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // End the table
        $label .= '</TABLE>>';

        return $label;
    }

    /**
     * @param $o
     *
     * @return array
     */
    private function getFunctionsModifier($o)
    {
        $functions = array();
        /** @var plPhpFunction $function */
        foreach ($o->functions as $function) {
            if ($function->return) {
                $return = ' : '.$function->return;
            }
            $functions[] = $this->getModifierRepresentation($function->modifier).$function->name.$this->getParamRepresentation($function->params).$return;
        }

        return $functions;
    }

    /**
     * @param $o
     *
     * @return string
     */
    private function getParametersAssociationDefinition($o)
    {
        $def = '';
        /** @var plPhpFunction $function */
        foreach ($o->functions as $function) {
            if ($this->options->createAssociations === false) {
                continue;
            }
            foreach ($function->params as $param) {
                foreach ($param->type->getTypeHints() as $typeHint) {
                    $typeName = $typeHint->getClassName();
                    if ($typeName !== null && array_key_exists($typeName, $this->structure) &&
                        !$this->existAssociaton($typeName, $o->name)
                    ) {
                        $def .= $this->createNodeRelation(
                            $this->getUniqueId($this->structure[$typeName]),
                            $this->getUniqueId($o),
                            array(
                                'dir' => 'back',
                                'arrowtail' => 'vee',
                                'style' => 'dashed',
                            )
                        );
                        $this->storeAssociaton($typeName, $o->name);
                    }
                }
            }
        }

        return $def;
    }

    /**
     * @param $o
     *
     * @return string
     */
    private function getReturnAssociationDefinition($o)
    {
        $def = '';
        /** @var plPhpFunction $function */
        foreach ($o->functions as $function) {
            if ($this->options->createAssociations === false) {
                continue;
            }
            $typeHintList = $function->return->getTypeHints();
            foreach ($typeHintList as $typeHint) {
                $className = $typeHint->getClassName();
                if (array_key_exists($className, $this->structure) &&
                    !$this->existAssociaton($className, $o->name)) {
                    $def .= $this->createNodeRelation(
                        $this->getUniqueId($this->structure[$className]),
                        $this->getUniqueId($o),
                        array(
                            'dir' => 'back',
                            'arrowtail' => 'vee',
                            'style' => 'dashed',
                        )
                    );
                    $this->storeAssociaton($className, $o->name);
                }
            }
        }

        return $def;
    }

    /**
     * @param $o
     *
     * @return array
     */
    private function getAttributesModifers($o)
    {
        $attributes = array();
        foreach ($o->attributes as $attribute) {
            $attributes[] = $this->getModifierRepresentation($attribute->modifier).$attribute->name;
        }

        return $attributes;
    }

    /**
     * @param $o
     *
     * @return string
     */
    private function getAttributesAssociationDefinition(PhpClass $o)
    {
        $def = '';
        foreach ($o->attributes as $attribute) {
            // Association creation is optional
            if ($this->options->createAssociations === false) {
                continue;
            }

            // Create associations if the attribute type is set
            if ($attribute->type !== null && array_key_exists($attribute->type, $this->structure) &&
                !$this->existAssociaton($attribute->type, $o->name)
            ) {
                $def .= $this->createNodeRelation(
                    $this->getUniqueId($this->structure[$attribute->type]),
                    $this->getUniqueId($o),
                    array(
                        'dir' => 'back',
                        'arrowtail' => 'vee',
                        'style' => 'dashed',
                    )
                );
                $this->storeAssociaton($attribute->type, $o->name);
            }
        }

        return $def;
    }

    /**
     * @param string $object1
     * @param string $object2
     */
    private function storeAssociaton($object1, $object2)
    {
        $this->associations[$object1.'-'.$object2] = true;
    }

    /**
     * @param string $object1
     * @param string $object2
     *
     * @return bool
     */
    private function existAssociaton($object1, $object2)
    {
        if (isset($this->associations[$object1.'-'.$object2])) {
            return true;
        }

        return false;
    }

    /**
     * @param PhpClass $class
     *
     * @return string
     */
    private function getClassExtendAndImplement(\Phuml\Generator\PhpClass $class)
    {
        $def = '';
        if ($class->extends !== null) {
            // Check if we need an "external" class node
            if (in_array($class->extends, $this->structure) !== true) {
                $def .= $this->getClassDefinition($class->extends);
            }

            $def .= $this->createNodeRelation(
                $this->getUniqueId($class->extends),
                $this->getUniqueId($class),
                array(
                    'dir' => 'back',
                    'arrowtail' => 'empty',
                    'style' => 'solid',
                )
            );
        }

        // Create class implements relation
        foreach ($class->implements as $interface) {
            // Check if we need an "external" interface node
            if (in_array($interface, $this->structure) !== true) {
                $def .= $this->getInterfaceDefinition($interface);
            }

            $def .= $this->createNodeRelation(
                $this->getUniqueId($interface),
                $this->getUniqueId($class),
                array(
                    'dir' => 'back',
                    'arrowtail' => 'normal',
                    'style' => 'dashed',
                )
            );
        }

        return $def;
    }
}
