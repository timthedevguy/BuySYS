<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use AppBundle\Entity\ChapterEntity;
use AppBundle\Entity\TopicEntity;

class PagesController extends Controller
{
    /**
     * @Route("/pages/chapter/new", name="new_chapter")
     */
    public function newChapterAction(Request $request)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = new ChapterEntity();

        $form = $this->createFormBuilder($chapter)
            ->add('chapternumber', TextType::class, array('label'  => 'Chapter Number'))
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

            $em->persist($chapter);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/chapter_new.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => '', 'topic_slug' => '', 'chapter' => null));
    }

    /**
     * @Route("/pages/chapter/{id}/topic/new", name="new_topic")
     * @Template()
     */
    public function newTopicAction(Request $request, $id)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->find($id);
        $topic = new TopicEntity();

        $form = $this->createFormBuilder($topic)
            ->add('topicnumber', TextType::class, array('label'  => 'Topic Number'))
            ->add('slug', TextType::class)
            ->add('title', TextType::class)
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
         'chapter_slug' => $chapter->getSlug(), 'topic_slug' => '', 'chapter' => $chapter));
    }

    /**
     * @Route("/pages/topic/edit/{id}", name="edit_topic")
     * @Template()
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
            ->add('content', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Save Topic'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine('default')->getManager();
            $topic->setModifiedOn(new \DateTime());
            $topic->setAuthorId($this->getUser()->getId());

            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/topic_edit.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => '', 'topic_slug' => '', 'chapter' => null, 'topic' => null));
    }

    /**
     * @Route("/pages/chapter/delete/{id}", name="delete_chapter")
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

            $em->remove($chapter);
            $em->flush();

            return $this->redirectToRoute('pages');
        }

        return $this->render('pages/chapter_delete.html.twig', array('form' => $form->createView(),'toc' => $chapters->findAll(),
         'chapter_slug' => $chapter->getSlug(), 'topic_slug' => '', 'chapter' => $chapter));
    }

    /**
     * @Route("/pages/{chapter_slug}/{topic_slug}", name="pages", defaults={"chapter_slug" = "home", "topic_slug" = ""})
     */
    public function showAction(Request $request, $chapter_slug, $topic_slug)
    {
        $chapters = $this->getDoctrine()->getRepository('AppBundle:ChapterEntity', 'default');
        $chapter = $chapters->findOneBySlug($chapter_slug);

        if($chapter == null)
        {
            return $this->render('pages/chapter_error.html.twig', array(
                'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
                 'chapter_slug' => $chapter_slug, 'chapter' => null));
        }

        $topics = $this->getDoctrine()->getRepository('AppBundle:TopicEntity', 'default');
        $topic = $topics->findTopicBySlugAndChapterId($topic_slug, $chapter->getId());

        if($topic == null & $topic_slug != "")
        {
            return $this->render('pages/topic_error.html.twig', array(
                'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
                'chapter_slug' => $chapter_slug, 'topic_slug' => $topic_slug, 'chapter' => $chapter));
        }
        elseif($topic_slug == "")
        {
            return $this->render('pages/index.html.twig', array(
                'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
                'chapter_slug' => $chapter_slug, 'topic_slug' => $topic_slug, 'chapter' => $chapter));
        }

        $topic = $topic[0];

        return $this->render('pages/page.html.twig', array(
            'page_name' => 'test', 'sub_text' => 'Alliance/Corp sponsered fleet operations', 'toc' => $chapters->findAll(),
             'chapter_slug' => $chapter->getSlug(), 'topic_slug' => $topic_slug, 'chapter' => $chapter, 'topic' => $topic));
    }


}
