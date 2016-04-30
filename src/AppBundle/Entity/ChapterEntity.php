<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

use AppBundle\Entity\TopicEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="chapters")
 */
class ChapterEntity
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
    protected $chapterNumber;

    public function setChapterNumber($chapterNumber)
    {
        $this->chapterNumber = $chapterNumber;

        return $this;
    }

    public function getChapterNumber()
    {
        return $this->chapterNumber;
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
     * @ORM\OneToMany(targetEntity="TopicEntity", mappedBy="chapter")
     * @ORM\OrderBy({"topicNumber" = "asc"})
     */
    protected $topics;

    public function __construct()
    {
        $this->topics = new ArrayCollection();
    }

    public function addTopic(TopicEntity $topic)
    {
        $topic->setChapter($this);
        $this->topics[] = $topic;

        return $this;
    }

    public function removeTopic(TopicEntity $topic)
    {
        $this->topics->removeElement($topic);
    }

    public function getTopics()
    {
        return $this->topics;
    }
}
