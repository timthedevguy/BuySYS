<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

use AppBundle\Entity\ChapterEntity;
use AppBundle\Entity\TopicEntity;

class PagesController extends Controller
{
    /**
     * @Route("/pages/chapter/new", name="new_chapter")
     * @Security("has_role('ROLE_EDITOR')")
     */
    public function newChapterAction(Request $request)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = new ChapterEntity();

        $form = $this->createFormBuilder($chapter)
            ->add('slug', TextType::class)
            ->add('title', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Create Chapter'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();

            $chapter->setCreatedOn(new \DateTime());
            $chapter->setModifiedOn(new \DateTime());
            $chapter->setAuthorId($this->getUser()->getId());

            if(count($chapters->findAll()) == 0)
            {
                $chapter->setChapterNumber(0);
            }
            else
            {

                $chapter->setChapterNumber($chapters->findAllArray()->last()->getChapterNumber() +1);
            }

            $eChapter = $chapters->findOneByChapterNumber($chapter->getChapterNumber());

            if($eChapter != null)
            {
                foreach($chapters->findAll() as $tChapter)
                {
                    if((int)$tChapter->getChapterNumber() >= (int)$chapter->getChapterNumber())
                    {
                        $tChapter->setChapterNumber($tChapter->getChapterNumber()+1);
                    }
                }
            }

            $em->persist($chapter);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/chapter_new.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter' => null));
    }

    /**
     * @Route("/pages/chapter/{id}/topic/new", name="new_topic")
     * @Security("has_role('ROLE_EDITOR')")
     */
    public function newTopicAction(Request $request, $id)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->find($id);
        $topic = new TopicEntity();

        $form = $this->createFormBuilder($topic)
            ->add('slug', TextType::class)
            ->add('title', TextType::class)
            ->add('isPublic', CheckboxType::class, array(
                'label'    => 'Make this topic available to the public?',
                'required' => false,
            ))
            ->add('content', TextareaType::class, array('attr' => array('id' => 'fckedit')))
            ->add('save', SubmitType::class, array('label' => 'Create Topic'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();
            $topic->setCreatedOn(new \DateTime());
            $topic->setModifiedOn(new \DateTime());
            $topic->setAuthorId($this->getUser()->getId());

            if(count($chapter->getTopics()) == 0)
            {
                $topic->setTopicNumber(1);
            }
            else
            {
                $topic->setTopicNumber($chapter->getTopics()->last()->getTopicNumber()+1);
            }

            $chapter->addTopic($topic);
            $em->persist($topic);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/topic_new.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => $chapter->getSlug(), 'topic_slug' => '', 'chapter' => $chapter));
    }

    /**
     * @Route("/pages/chapter/edit/{id}", name="edit_chapter")
     * @Security("has_role('ROLE_EDITOR')")
     */
    public function editChapterAction(Request $request, $id)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->find($id);

        $form = $this->createFormBuilder($chapter)
            ->add('chapternumber', TextType::class, array('label'  => 'Chapter Number'))
            ->add('slug', TextType::class)
            ->add('title', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Save Chapter'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();

            $chapter->setModifiedOn(new \DateTime());
            $chapter->setAuthorId($this->getUser()->getId());

            //$em->persist($chapter);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/chapter_edit.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter' => $chapter));
    }

    /**
     * @Route("/pages/topic/edit/{id}", name="edit_topic")
     * @Security("has_role('ROLE_EDITOR')")
     */
    public function editTopicAction(Request $request, $id)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $topics = $this->getDoctrine()->getRepository('AppBundle:TopicEntity', 'default');

        $topic = $topics->find($id);

        $form = $this->createFormBuilder($topic)
            ->add('topicnumber', TextType::class, array('label'  => 'Topic Number'))
            ->add('slug', TextType::class)
            ->add('title', TextType::class)
            ->add('isPublic', CheckboxType::class, array(
                'label'    => 'Make this topic available to the public?',
                'required' => false,
            ))
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Save Topic'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();
            $topic->setModifiedOn(new \DateTime());
            $topic->setAuthorId($this->getUser()->getId());

            if($topic->getChapter()->countTopics($topic->getTopicNumber()) > 1)
            {
                foreach($topic->getChapter()->getTopics() as $tTopic)
                {
                    if($tTopic->getTopicNumber() >= $topic->getTopicNumber() & $tTopic->getId() != $topic->getId())
                    {
                        $tTopic->setTopicNumber($tTopic->getTopicNumber()+1);
                    }
                }
            }

            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/topic_edit.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => $topic->getChapter()->getSlug(), 'topic_slug' => '', 'chapter' => null, 'topic' => null));
    }

    /**
     * @Route("/pages/chapter/delete/{id}", name="delete_chapter")
     * @Security("has_role('ROLE_EDITOR')")
     */
    public function deleteChapterAction(Request $request, $id)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->find($id);

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_chapter', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('save', SubmitType::class, array('label' => 'Confirm Delete', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();

            foreach($chapter->getTopics() as $topic)
            {
                $em->remove($topic);
            }

            $em->remove($chapter);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/chapter_delete.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => $chapter->getSlug(), 'topic_slug' => '', 'chapter' => $chapter));
    }

    /**
     * @Route("/pages/topic/delete/{id}", name="delete_topic")
     * @Security("has_role('ROLE_EDITOR')")
     */
    public function deleteTopicAction(Request $request, $id)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $topics = $this->getDoctrine()->getRepository('AppBundle:TopicEntity', 'default');
        $topic = $topics->find($id);
        $chapter = $topic->getChapter();

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('delete_topic', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('save', SubmitType::class, array('label' => 'Confirm Delete', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();

            $em->remove($topic);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/topic_delete.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => $chapter->getSlug(), 'topic_slug' => '', 'chapter' => $chapter));
    }

    /**
     * @Route("/pages/{chapter_slug}/{topic_slug}", name="pages", defaults={"chapter_slug" = "home", "topic_slug" = ""})
     */
    public function showAction(Request $request, $chapter_slug, $topic_slug)
    {
        $template = 'pages/page.html.twig';
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->findOneBySlug($chapter_slug);

        if($chapter == null)
        {
            return $this->render('pages/chapter_error.html.twig', array('toc' => $chapters->findAll(),
                 'chapter_slug' => $chapter_slug, 'chapter' => null));
        }

        $topics = $this->getDoctrine()->getRepository('AppBundle:TopicEntity', 'default');
        $topic = $topics->findTopicBySlugAndChapterId($topic_slug, $chapter->getId());

        if($topic == null & $topic_slug != "")
        {
            return $this->render('pages/topic_error.html.twig', array(
                'toc' => $chapters->findAll(), 'chapter_slug' => $chapter_slug, 'topic_slug' => $topic_slug, 'chapter' => $chapter));
        }
        elseif($topic_slug == "")
        {
            if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
            {
                throw $this->createAccessDeniedException();
            }

            return $this->render('pages/index.html.twig', array('toc' => $chapters->findAll(),
                'chapter_slug' => $chapter_slug, 'topic_slug' => $topic_slug, 'chapter' => $chapter));
        }

        $topic = $topic[0];

        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY'))
        {
            $template = 'pages/page_public.html.twig';

            if($topic->getIsPublic() == false)
            {
                throw $this->createAccessDeniedException();
            }
        }

        return $this->render($template, array('toc' => $chapters->findAll(),
             'chapter_slug' => $chapter->getSlug(), 'topic_slug' => $topic_slug, 'chapter' => $chapter, 'topic' => $topic,
            'nexttopic' => $chapter->nextTopic($topic), 'prevtopic' => $chapter->previousTopic($topic)));
    }


}
