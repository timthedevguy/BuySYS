<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use AppBundle\Entity\TransactionEntity;
use AppBundle\Entity\UserPrefecEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @UniqueEntity("username", message="This character has already been registered.")
 * @UniqueEntity("characterId", message="This character has already been registered.")
 */
class UserEntity implements AdvancedUserInterface, \Serializable
{

    //CONSTRUCTOR
    public function _construct() {
        $this->isActive = true;
        $this->role = "ROLE_USER";
        $this->transactions = new ArrayCollection();
    }



    //FIELDS
    /**
    * @ORM\Column(name="id", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $username;
    /**
    * @ORM\Column(type="string", length=50)
    */
    protected $role;
    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $overrideRole = "";
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $entitlements = "";
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $overrideEntitlements = "";
    /**
    * @ORM\Column(type="boolean")
    */
    protected $isActive;
    /**
     * @ORM\Column(type="integer")
     */
    protected $characterId;
    /**
     * @ORM\Column(type="datetime")
     */
    protected $lastLogin;
    /**
     * @ORM\OneToMany(targetEntity="TransactionEntity", mappedBy="user")
     */
    protected $transactions;
    /**
     * @ORM\OneToOne(targetEntity="UserPreferencesEntity", mappedBy="user")
     */
    protected $preferences;



    //GETTERS AND SETTERS
    public function getId()
    {
        return $this->id;
    }

    public function setCharacterId($characterId)
    {
        $this->characterId = $characterId;
        return $this;
    }
    public function getCharacterId()
    {
        return $this->characterId;
    }

    public function setLastLogin($lastLogin)
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }
    public function getUsername()
    {
        return $this->username;
    }

    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }
    public function getRole()
    {
        return $this->role;
    }

    public function getOverrideRole()
    {
        return $this->overrideRole;
    }
    public function setOverrideRole($overrideRole)
    {
        $this->overrideRole = $overrideRole;
        return $this;
    }

    public function getEntitlements()
    {
        return $this->entitlements;
    }
    public function setEntitlements($entitlements)
    {
        $this->entitlements = $entitlements;
        return $this;
    }
    public function addEntitlement($entitlement)
    {
        $this->entitlements .= ",".$entitlement;
        return $this;
    }

    public function getOverrideEntitlements()
    {
        return $this->overrideEntitlements;
    }
    public function setOverrideEntitlements($overrideEntitlements)
    {
        $this->overrideEntitlements = $overrideEntitlements;
        return $this;
    }
    public function addOverrideEntitlement($entitlement)
    {
        $this->overrideEntitlements .= ",".$entitlement;
        return $this;
    }

    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }
    public function getIsActive()
    {
        return $this->isActive;
    }

    public function addTransaction(\AppBundle\Entity\TransactionEntity $transaction)
    {
        $this->transactions[] = $transaction;
        return $this;
    }
    public function removeTransaction(\AppBundle\Entity\TransactionEntity $transaction)
    {
        $this->transactions->removeElement($transaction);
    }
    public function getTransactions()
    {
        return $this->transactions;
    }

    public function setUserPreferences(\AppBundle\Entity\UserPreferencesEntity $preferences)
    {
        $this->preferences = $preferences;
        return $this;
    }
    public function getUserPreferences()
    {
        return $this->preferences;
    }



    //USED BY SYMFONY
    public function getSalt()
    {
        return null;
    }
    public function getRoles()
    {
        if (!empty($this->overrideRole))
        {
            $roles = explode(",", $this->overrideRole);
        }
        else
        {
            $roles = explode(",", $this->role);
        }

        if (!empty($this->overrideEntitlements))
        {
            $entitlements = explode(",", $this->overrideEntitlements);
        }
        else
        {
            $entitlements = explode(",", $this->entitlements);
        }

        return array_merge($roles, $entitlements); //merge in entitlements (they're basically just more roles)
    }
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->isActive
            // see section on salt below
            // $this->salt,
        ));
    }
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->isActive
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }
    public function getPassword()
    {
        return null;
    }
    public function isAccountNonExpired()
    {
        return true;
    }
    public function isAccountNonLocked()
    {
        return true;
    }
    public function isCredentialsNonExpired()
    {
        return true;
    }
    public function isEnabled()
    {
        return $this->isActive;
    }
    public function eraseCredentials()
    {
    }
}
