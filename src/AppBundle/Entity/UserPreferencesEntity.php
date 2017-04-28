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
 * @ORM\Table(name="userPreferences")
 */
class UserEntity implements AdvancedUserInterface, \Serializable
{


    public function _construct() {

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


}
