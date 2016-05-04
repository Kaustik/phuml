<?php

use Phuml\Generator\StructureTokenparserGenerator;

abstract class plStructureGenerator 
{
    public static function factory( $generator ) 
    {
        $classname = 'plStructure' . ucfirst( $generator ) . 'Generator';
        if ($generator == 'tokenparser') {
            return new StructureTokenparserGenerator();
        }
        if ( class_exists( $classname ) === false ) 
        {
            throw new plStructureGeneratorNotFoundException( $generator );
        }
        return new $classname();
    }

    public abstract function createStructure( array $files );    
}

?>
