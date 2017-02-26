<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use AppBundle\Entity\LineItemEntity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TransactionRepository")
 * @ORM\Table(name="transactions")
 */
class TransactionEntity
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
     * @ORM\ManyToOne(targetEntity="UserEntity", inversedBy="transactions")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $user;

    public function setUser(UserEntity $user = null)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected $orderId;

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getOrderId()
    {
        return $this->orderId;
    }
    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $type;

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @ORM\Column(type="string", length=25)
     */
    protected $status;

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @ORM\Column(type="boolean")
     */
    protected $is_complete;

    public function setIsComplete($is_complete)
    {
        $this->is_complete = $is_complete;

        return $this;
    }

    public function getIsComplete()
    {
        return $this->is_complete;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @ORM\OneToMany(targetEntity="LineItemEntity", mappedBy="transaction")
     */
    protected $lineitems;

    public function __construct()
    {
        $this->lineitems = new ArrayCollection();
        $this->setStatus = "Pending";
    }


    /**
     * Add lineitem
     *
     * @param \AppBundle\Entity\TransactionEntity $lineitem
     *
     * @return TransactionEntity
     */
    public function addLineitem(LineItemEntity $lineitem)
    {
        $lineitem->setTransaction($this);
        $this->net += $lineitem->getNetPrice();
        $this->gross += $lineitem->getGrossPrice();
        $this->lineitems[] = $lineitem;

        return $this;
    }

    /**
     * Remove lineitem
     *
     * @param \AppBundle\Entity\TransactionEntity $lineitem
     */
    public function removeLineitem(LineItemEntity $lineitem)
    {
        $this->lineitems->removeElement($lineitem);
    }

    /**
     * Get lineitems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLineitems()
    {
        return $this->lineitems;
    }

    /**
     * @ORM\Column(type="bigint")
     */
    protected $gross;

    public function setGross($gross)
    {
        $this->gross = $gross;

        return $this;
    }

    public function getGross()
    {
        return $this->gross;
    }

    /**
     * @ORM\Column(type="bigint")
     */
    protected $net;

    public function setNet($net)
    {
        $this->net = $net;

        return $this;
    }

    public function getNet()
    {
        return $this->net;
    }
}
