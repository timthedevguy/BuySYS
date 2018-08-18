<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RuleRepository")
 * @ORM\Table(name="rule")
 */
class RuleEntity
{
    //FIELDS
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer")
     */
    protected $sort;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $target;
    /**
     * @ORM\Column(type="integer")
     */
    protected $targetId;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $targetName;
    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $attribute;
    /**
     * @ORM\Column(type="decimal", precision=19, scale=2)
     */
    protected $value;
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $ruleType;


    //GETTTERS AND SETTERS
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
    public function getId()
    {
        return $this->id;
    }

    public function setSort($sort)
    {
        $this->sort = $sort;
        return $this;
    }
    public function getSort()
    {
        return $this->sort;
    }

    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }
    public function getTarget()
    {
        return $this->target;
    }

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;
        return $this;
    }
    public function getTargetId()
    {
        return $this->targetId;
    }
    
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;
        return $this;
    }
    public function getTargetName()
    {
        return $this->targetName;
    }

    public function setAttribute($attribute)
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function getAttribute()
    {
        return $this->attribute;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getRuleType()
    {
        return $this->ruleType;
    }
    public function setRuleType($ruleType)
    {
        $this->ruleType = $ruleType;
        return $this;
    }
}
