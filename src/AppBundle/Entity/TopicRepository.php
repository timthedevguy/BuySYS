<?php
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

class TopicRepository extends EntityRepository {

    public function findTopicBySlugAndChapterId($topic_slug, $chapter_id) {

        return $this->getEntityManager()
            ->createQuery(
                'SELECT t FROM AppBundle:TopicEntity t WHERE t.chapter = (:chapterid) AND t.slug = (:slug)'
            )->setParameter('chapterid', $chapter_id)->setParameter('slug', $topic_slug)->getResult();
    }
}
