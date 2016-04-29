<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use AppBundle\Entity\ChapterEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="topics")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\TopicRepository")
 */
class TopicEntity
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
     * @ORM\Column(type="string", length=255)
     */
    protected $slug;

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $title;

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @ORM\Column(type="text")
     */
    protected $content;

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $createdOn;

    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @ORM\Column(type="datetime")
     */
    protected $modifiedOn;

    public function setModifiedOn($modifiedOn)
    {
        $this->modifiedOn = $modifiedOn;

        return $this;
    }

    public function getModifiedOn()
    {
        return $this->modifiedOn;
    }

    /**
     * @ORM\Column(type="integer")
     */
    protected $authorID;

    public function setAuthorID($authorID)
    {
        $this->authorID = $authorID;

        return $this;
    }

    public function getAuthorID()
    {
        return $this->authorID;
    }

    /**
     * @ORM\ManyToOne(targetEntity="ChapterEntity", inversedBy="Topics")
     * @ORM\JoinColumn(name="chapter_id", referencedColumnName="id")
     */
    protected $chapter;

    public function setChapter(ChapterEntity $chapter = null)
    {
        $this->chapter = $chapter;

        return $this;
    }

    public function getChapter()
    {
        return $this->chapter;
    }
}
