<?php

namespace Phuml\Generator;

class PhpProperty
{

    public function getFormattedName(){
        $namespace = $this->namespace;
        if (!empty($namespace)) {
            $namespace .= '\\';
        }
        $namespace = str_replace('\\','\\\\',$namespace);
        return $namespace.$this->name;
    }
}
