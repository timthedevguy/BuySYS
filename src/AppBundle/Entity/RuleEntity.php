<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\RuleRepository")
 * @ORM\Table(name="rule")
 */
class RuleEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @ORM\Column(type="integer")
     */
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

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $target;

    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $targetId;

    public function setTargetId($targetId)
    {
        $this->targetId = $targetId;

        return $this;
    }

    public function getTargetId()
    {
        return $this->targetId;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $targetName;
    
    public function setTargetName($targetName)
    {
        $this->targetName = $targetName;
    
        return $this;
    }
    
    public function getTargetName()
    {
        return $this->targetName;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
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

    /**
     * @ORM\Column(type="decimal", precision=19, scale=2)
     */
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