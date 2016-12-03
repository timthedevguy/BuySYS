<?php
namespace AppBundle\Model;

class TypeRuleModel {

    protected $typeId;
    
    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;
    
        return $this;
    }
    
    public function getTypeId()
    {
        return $this->typeId;
    }

    protected $sort;

    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort()
    {
        return $this->sort;
    }

    protected $attribute;

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;

        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    protected $value;

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }
}