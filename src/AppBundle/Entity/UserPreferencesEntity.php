<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="userPreferences")
 */
class UserPreferencesEntity {


    public function __construct() {

        $this->displayTheme = "default";
    }

    /**
    * @ORM\Column(name="id", type="integer", nullable=false)
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    protected $id;
    /**
    * @ORM\Column(type="string", length=50)
    */
    protected $displayTheme;



    /**
     * @ORM\OneToOne(targetEntity="UserEntity", inversedBy="preferences")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getDisplayTheme()
    {
        return $this->displayTheme;
    }

    /**
     * @param mixed $displayTheme
     */
    public function setDisplayTheme($displayTheme)
    {
        $this->displayTheme = $displayTheme;
    }
}
