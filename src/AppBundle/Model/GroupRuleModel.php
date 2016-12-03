<?php
namespace AppBundle\Model;

class GroupRuleModel {

    protected $marketGroupId;
    
    public function setMarketGroupId($marketGroupId)
    {
        $this->marketGroupId = $marketGroupId;
    
        return $this;
    }
    
    public function getMarketGroupId()
    {
        return $this->marketGroupId;
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