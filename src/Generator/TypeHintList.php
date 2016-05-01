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
        $this->filterOutDuplicates();
    }

    private function filterOutDuplicates()
    {
        $existingAlready = [];
        $filteredList = [];
        foreach ($this->typeHints as $typeHint) {
            if (!isset($existingAlready[$typeHint->getClassName()])) {
                $filteredList[] = $typeHint;
                $existingAlready[$typeHint->getClassName()] = true;
            }
        }
        $this->typeHints = $filteredList;
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
