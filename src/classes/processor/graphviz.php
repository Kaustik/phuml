<?php

class plGraphvizProcessor extends plProcessor 
{
    private $properties;

    private $output;

    private $structure;

    public $options;

    public function __construct() 
    {
        $this->options   = new plGraphvizProcessorOptions();

        $this->structure = null;
        $this->output    = null;
    }

    public function getInputTypes() 
    {
        return array( 
            'application/phuml-structure'
        );
    }

    public function getOutputType() 
    {
        return 'text/dot';
    }

    public function process( $input, $type ) 
    {
        $this->structure = $input;

        $this->output  = 'digraph "' . sha1( mt_rand() ) . '" {' . "\n";
        $this->output .= 'splines = true;' . "\n";
        $this->output .= 'overlap = false;' . "\n";
        $this->output .= 'mindist = 0.6;' . "\n";

        foreach( $this->structure as $object ) 
        {
            if ( $object instanceof plPhpClass ) 
            {
                $this->output .= $this->getClassDefinition( $object );
            } 
            else if ( $object instanceof plPhpInterface ) 
            {
                $this->output .= $this->getInterfaceDefinition( $object );
            }
        }

        $this->output .= "}";

        return $this->output;
    }

    private function getClassDefinition( $o ) 
    {
        $def = '';

        // First we need to create the needed data arrays
        $name = $o->name;

        $attributes = $this->getAttributesModifers($o);
        $associations = $this->getAttributesAssociations($o);
        $def .= $this->getAttributesAssociationDefinition($o, $associations);

        $functions = $this->getFunctionsModifier($o);
        $def .= $this->getParametersAssociationDefinition($o, $associations);
        $def .= $this->getReturnAssociationDefinition($o, $associations);

        // Create the node
        $def .= $this->createNode( 
            $this->getUniqueId( $o ),
            array(
                'label' => $this->createClassLabel( $name, $attributes, $functions ),
                'shape' => 'plaintext',
            )
        );

        // Create class inheritance relation
        if ( $o->extends !== null ) 
        {
            // Check if we need an "external" class node
            if ( in_array( $o->extends, $this->structure ) !== true ) 
            {
                $def .= $this->getClassDefinition( $o->extends );
            }

            $def .= $this->createNodeRelation( 
                $this->getUniqueId( $o->extends ),
                $this->getUniqueId( $o ),
                array( 
                    'dir'       => 'back',
                    'arrowtail' => 'empty',
                    'style'     => 'solid'
                )
            );
        }

        // Create class implements relation
        foreach( $o->implements as $interface ) 
        {
            // Check if we need an "external" interface node
            if ( in_array( $interface, $this->structure ) !== true ) 
            {
                $def .= $this->getInterfaceDefinition( $interface );
            }

            $def .= $this->createNodeRelation( 
                $this->getUniqueId( $interface ),
                $this->getUniqueId( $o ),
                array( 
                    'dir'       => 'back',
                    'arrowtail' => 'normal',
                    'style'     => 'dashed',
                )
            );
        }

        return $def;
    }

    private function getInterfaceDefinition( $o ) 
    {
        $def = '';

        // First we need to create the needed data arrays
        $name = $o->name;

        $associations = [];
        $functions = $this->getFunctionsModifier($o);
        $def .= $this->getParametersAssociationDefinition($o, $associations);

        // Create the node
        $def .= $this->createNode( 
            $this->getUniqueId( $o ),
            array(
                'label' => $this->createInterfaceLabel( $name, $associations, $functions ),
                'shape' => 'plaintext',
            )
        );

        // Create interface inheritance relation        
        if ( $o->extends !== null ) 
        {
            // Check if we need an "external" interface node
            if ( in_array( $o->extends, $this->structure ) !== true ) 
            {
                $def .= $this->getInterfaceDefinition( $o->extends );
            }

            $def .= $this->createNodeRelation( 
                $this->getUniqueId( $o->extends ),
                $this->getUniqueId( $o ),
                array( 
                    'dir'       => 'back',
                    'arrowtail' => 'empty',
                    'style'     => 'solid'
                )
            );
        }

        return $def;
    }

    private function getModifierRepresentation( $modifier ) 
    {
        return ( $modifier === 'public' )
               ? ( '+' )
               : ( ( $modifier === 'protected' )
                 ? ( '#' )
                 : ( '-' ) );
    }

    private function getParamRepresentation( $params ) 
    {
        if ( count( $params ) === 0 ) 
        {
            return '()';
        }

        $representation = '( ';
        $lineLength = 0;
        for( $i = 0; $i<count( $params ); $i++ ) 
        {
            if ( $params[$i]->type !== null ) 
            {
                $representation .= $params[$i]->type . ' ';
            }

            $representation .= $params[$i]->name;
            if ( $i < count( $params ) - 1 ) 
            {
                $representation .= ', ';
            }
            
            if (strlen($representation) - $lineLength > 30){
                //newline each 2 line
                $lineLength = strlen($representation);
                $representation .= '<BR />';
            }
        }
        $representation .= ' )';

        return $representation;
    }

    private function getUniqueId( $object ) 
    {
        return '"' . spl_object_hash( $object ) . '"';
    }

    private function createNode( $name, $options ) 
    {
        $node = $name . " [";
        foreach( $options as $key => $value ) 
        {
            $node .= $key . '=' . $value . ' ';
        }
        $node .= "]\n";
        return $node;
    }

    private function createNodeRelation( $node1, $node2, $options ) 
    {
        $relation = $node1 . ' -> ' . $node2 . ' [';
        foreach( $options as $key => $value ) 
        {
            $relation .= $key . '=' . $value . ' ';
        }
        $relation .= "]\n";
        return $relation;
    }

    private function createInterfaceLabel( $name, $attributes, $functions )     
    {
        // Start the table
        $label = '<<TABLE CELLSPACING="0" BORDER="0" ALIGN="LEFT">';
        
        // The title
        $label .= '<TR><TD BORDER="' . $this->options->style->interfaceTableBorder . '" ALIGN="CENTER" BGCOLOR="' . $this->options->style->interfaceTitleBackground . '"><FONT COLOR="' . $this->options->style->interfaceTitleColor . '" FACE="' . $this->options->style->interfaceTitleFont . '" POINT-SIZE="' . $this->options->style->interfaceTitleFontsize . '">' . $name . '</FONT></TD></TR>';

        // The attributes block
        $label .= '<TR><TD BORDER="' . $this->options->style->interfaceTableBorder . '" ALIGN="LEFT" BGCOLOR="' . $this->options->style->interfaceAttributesBackground . '">';
        if ( count( $attributes ) === 0 ) 
        {
            $label .= ' ';
        }
        foreach( $attributes as $attribute ) 
        {
            $label .= '<FONT COLOR="' . $this->options->style->interfaceAttributesColor . '" FACE="' . $this->options->style->interfaceAttributesFont . '" POINT-SIZE="' . $this->options->style->interfaceAttributesFontsize . '">' . $attribute . '</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // The function block
        $label .= '<TR><TD BORDER="' . $this->options->style->interfaceTableBorder . '" ALIGN="LEFT" BGCOLOR="' . $this->options->style->interfaceFunctionsBackground . '">';
        if ( count( $functions ) === 0 ) 
        {
            $label .= ' ';
        }
        foreach( $functions as $function ) 
        {
            $label .= '<FONT COLOR="' . $this->options->style->interfaceFunctionsColor . '" FACE="' . $this->options->style->interfaceFunctionsFont . '" POINT-SIZE="' . $this->options->style->interfaceFunctionsFontsize . '">' . $function . '</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // End the table
        $label .= '</TABLE>>';

        return $label;
    }

    private function createClassLabel( $name, $attributes, $functions )     
    {
        // Start the table
        $label = '<<TABLE CELLSPACING="0" BORDER="0" ALIGN="LEFT">';
        
        // The title
        $label .= '<TR><TD BORDER="' . $this->options->style->classTableBorder . '" ALIGN="CENTER" BGCOLOR="' . $this->options->style->classTitleBackground . '"><FONT COLOR="' . $this->options->style->classTitleColor . '" FACE="' . $this->options->style->classTitleFont . '" POINT-SIZE="' . $this->options->style->classTitleFontsize . '">' . $name . '</FONT></TD></TR>';

        // The attributes block
        $label .= '<TR><TD BORDER="' . $this->options->style->classTableBorder . '" ALIGN="LEFT" BGCOLOR="' . $this->options->style->classAttributesBackground . '">';
        if ( count( $attributes ) === 0 ) 
        {
            $label .= ' ';
        }
        foreach( $attributes as $attribute ) 
        {
            $label .= '<FONT COLOR="' . $this->options->style->classAttributesColor . '" FACE="' . $this->options->style->classAttributesFont . '" POINT-SIZE="' . $this->options->style->classAttributesFontsize . '">' . $attribute . '</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // The function block
        $label .= '<TR><TD BORDER="' . $this->options->style->classTableBorder . '" ALIGN="LEFT" BGCOLOR="' . $this->options->style->classFunctionsBackground . '">';
        if ( count( $functions ) === 0 ) 
        {
            $label .= ' ';
        }
        foreach( $functions as $function ) 
        {
            $label .= '<FONT COLOR="' . $this->options->style->classFunctionsColor . '" FACE="' . $this->options->style->classFunctionsFont . '" POINT-SIZE="' . $this->options->style->classFunctionsFontsize . '">' . $function . '</FONT><BR ALIGN="LEFT"/>';
        }
        $label .= '</TD></TR>';

        // End the table
        $label .= '</TABLE>>';

        return $label;
    }

    /**
     * @param $o
     * @return array
     */
    private function getFunctionsModifier($o)
    {
        $functions = array();
        foreach ($o->functions as $function) {
            $return = '';
            if ($function->return) {
                $return = ' : '.$function->return;
            }
            $functions[] = $this->getModifierRepresentation($function->modifier) . $function->name . $this->getParamRepresentation($function->params). $return;
        }
        return $functions;
    }

    /**
     * @param $o
     * @param array $associations
     * @return string
     */
    private function getParametersAssociationDefinition($o, $associations)
    {
        $def = '';
        foreach ($o->functions as $function) {
            if ($this->options->createAssociations === false) {
                continue;
            }
            foreach ($function->params as $param) {
                if ($param->type !== null && array_key_exists($param->type, $this->structure) && !array_key_exists(strtolower($param->type), $associations)) {
                    $def .= $this->createNodeRelation(
                        $this->getUniqueId($this->structure[$param->type]),
                        $this->getUniqueId($o),
                        array(
                            'dir' => 'back',
                            'arrowtail' => 'none',
                            'style' => 'dashed',
                        )
                    );
                    $associations[strtolower($param->type)] = true;
                }
            }
        }
        return $def;
    }

    /**
     * @param $o
     * @param array $associations
     * @return string
     */
    private function getReturnAssociationDefinition($o, $associations)
    {
        $def = '';
        foreach ($o->functions as $function) {
            if ($this->options->createAssociations === false) {
                continue;
            }
            if ($function->return) {
                if (array_key_exists($function->return, $this->structure) && !array_key_exists(strtolower($function->return), $associations)) {
                    $def .= $this->createNodeRelation(
                        $this->getUniqueId($this->structure[$function->return]),
                        $this->getUniqueId($o),
                        array(
                            'dir' => 'back',
                            'arrowtail' => 'none',
                            'style' => 'dashed',
                        )
                    );
                    $associations[strtolower($function->return)] = true;
                }
            }
        }
        return $def;
    }
    
    

    /**
     * @param $o
     * @return array
     */
    private function getAttributesModifers($o)
    {
        $attributes = array();
        foreach ($o->attributes as $attribute) {
            $attributes[] = $this->getModifierRepresentation($attribute->modifier) . $attribute->name;
        }
        return $attributes;
    }

    /**
     * @param $o
     * @param $associations
     * @param $def
     * @return string
     */
    private function getAttributesAssociationDefinition($o, $associations)
    {
        $def = '';
        foreach ($o->attributes as $attribute) {
            // Association creation is optional
            if ($this->options->createAssociations === false) {
                continue;
            }

            // Create associations if the attribute type is set
            if ($attribute->type !== null && array_key_exists($attribute->type, $this->structure) && !array_key_exists(strtolower($attribute->type), $associations)) {
                $def .= $this->createNodeRelation(
                    $this->getUniqueId($this->structure[$attribute->type]),
                    $this->getUniqueId($o),
                    array(
                        'dir' => 'back',
                        'arrowtail' => 'none',
                        'style' => 'dashed',
                    )
                );
            }
        }
        return $def;
    }

    /**
     * @param $o
     * @return array
     */
    private function getAttributesAssociations($o)
    {
        $associations = array();
        foreach ($o->attributes as $attribute) {
            if ($this->options->createAssociations === false) {
                continue;
            }
            if ($attribute->type !== null && array_key_exists($attribute->type, $this->structure) &&
                !array_key_exists(strtolower($attribute->type), $associations)
            ) {
                $associations[strtolower($attribute->type)] = true;
            }
        }
        return $associations;
    }
}
