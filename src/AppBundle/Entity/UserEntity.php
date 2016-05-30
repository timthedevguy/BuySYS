<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use AppBundle\Entity\TransactionEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class UserEntity implements AdvancedUserInterface, \Serializable
{
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
    * @ORM\Column(type="text")
    */
    protected $password;
    /**
    * @ORM\Column(type="string", length=255)
    */
    protected $email;
    /**
    * @ORM\Column(type="string", length=50)
    */
    protected $role;
    /**
    * @ORM\Column(type="boolean")
    */
    protected $isActive;

    /**
     * @ORM\Column(type="integer")
     */
     protected $characterId;

     public function setCharacterId($characterId)
     {
         $this->characterId = $characterId;

         return $this;
     }

     public function getCharacterId()
     {
         return $this->characterId;
     }

     /**
      * @ORM\Column(type="datetime")
      */
     protected $lastLogin;

     public function setLastLogin($lastLogin)
     {
         $this->lastLogin = $lastLogin;

         return $this;
     }

     public function getLastLogin()
     {
         return $this->lastLogin;
     }

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max = 4096)
     */
    private $plainPassword;

    private $character_name;
    private $api_key;
    private $api_code;

    public function setCharacterName($character_name)
    {
        $this->character_name = $character_name;

        return $this;
    }

    public function getCharacterName()
    {
        return $this->character_name;
    }

    public function setApiKey($api_key)
    {
        $this->api_key = $api_key;

        return $this;
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function setApiCode($api_code)
    {
        $this->api_code = $api_code;

        return $this;
    }

    public function getApiCode()
    {
        return $this->api_code;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $resetCode;

    public function setResetCode($resetCode)
    {
        $this->resetCode = $resetCode;

        return $this;
    }

    public function getResetCode()
    {
        return $this->resetCode;
    }

    public function _construct() {

        $this->isActive = true;
        $this->role = "ROLE_USER";
        $this->transactions = new ArrayCollection();
    }

    public function getSalt() {

        return null;
    }

    public function getRoles() {

        return explode(",", $this->role);
    }

    public function eraseCredentials() {
        $this->setPlainPassword(null);
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
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
            $this->password,
            $this->isActive,
            // see section on salt below
            // $this->salt
        ) = unserialize($serialized);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getMaskedEmail()
    {
        $plainEmail = $this->email;
        $maskedEmail = substr($plainEmail, 0, 3);

        for($i=0;$i<6;$i++)
        {
            $maskedEmail .= "*";
        }

        return $maskedEmail . "@" . explode("@", $plainEmail)[1];
    }

    /**
     * Set role
     *
     * @param string $role
     *
     * @return User
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return User
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
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

    /**
     * @ORM\OneToMany(targetEntity="TransactionEntity", mappedBy="user")
     */
    protected $transactions;

    public function addTransaction(\AppBundle\Entity\TransactionEntity $transaction)
    {
        $this->transactions[] = $transaction;

        return $this;
    }

    public function removeTransaction(\AppBundle\Entity\TransactionEntity $transaction)
    {
        $this->transactions->removeElement($transaction);
    }

    public function getTransaction()
    {
        return $this->transactions;
    }
}
