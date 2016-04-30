<?php

namespace Phuml\Generator;

class TypeHintList
{
    /**
     * @var TypeHint[]
     */
    private $typeHints = [];

    /**
     * TypeHintList constructor.
     *
     * @param TypeHint[] $typeHints
     */
    public function __construct(array $typeHints)
    {
        $this->typeHints = $typeHints;
    }

    /**
     * @return TypeHint[]
     */
    public function getTypeHints()
    {
        return $this->typeHints;
    }

    public function __toString()
    {
        $typeList = array_map(function (TypeHint $typeHint) {
            if ($typeHint->isIsArrayOfClass()) {
                return $typeHint->getClassName().'[]';
            }

            return $typeHint->getClassName();
        }, $this->typeHints);

        return implode('|', $typeList);
    }
}
