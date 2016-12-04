<?php
namespace AppBundle\Controller;

use AppBundle\Form\TestRuleForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Model\GroupRuleModel;
use AppBundle\Model\TypeRuleModel;
use AppBundle\Entity\RuleEntity;
use AppBundle\Form\AddGroupRuleForm;
use AppBundle\Form\AddTypeRuleForm;

class RuleController extends Controller
{
    /**
     * @Route("/admin/settings/rules", name="admin_buyback_rules")
     */
    public function indexAction(Request $request)
    {
        $results = null;
        $groupModel = new GroupRuleModel();
        $typeModel = new TypeRuleModel();

        $groupForm = $this->createForm(AddGroupRuleForm::class, $groupModel);
        $typeForm = $this->createForm(AddTypeRuleForm::class, $typeModel);
        $testForm = $this->createForm(TestRuleForm::class);

        $em = $this->getDoctrine()->getManager();

        if($request->getMethod() == "POST") {

            $form_results = $request->request->get('test_rule_form');

            if($form_results == null) {

                $form_results = $request->request->get('add_type_rule_form');
                $rule = new RuleEntity();

                if ($form_results == null) {

                    // Submitted form is Group Form
                    $form_results = $request->request->get('add_group_rule_form');
                    $rule->setTarget('group');
                    $rule->setTargetId($form_results['marketgroupid']);
                    $rule->setTargetName($this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity', 'evedata')->
                    findOneByMarketGroupID($form_results['marketgroupid'])->getMarketGroupName());

                    $this->addFlash('success', "Added new Group rule");
                } else {

                    // Submitted form was Type Form
                    $rule->setTarget('type');
                    $rule->setTargetId($form_results['typeid']);
                    $rule->setTargetName($this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->
                    findOneByTypeID($form_results['typeid'])->getTypeName());

                    $this->addFlash('success', "Added new Item rule");
                }

                $rule->setSort($this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->getNextSort());
                $rule->setAttribute($form_results['attribute']);
                $rule->setValue($form_results['value']);
                $em->persist($rule);
                $em->flush();
            } else {

                $results = $this->get('market')->ProcessBuybackRules($form_results['typeid']);
            }
        }

        $rules = $em->getRepository('AppBundle:RuleEntity', 'default')->findAllSortedBySort();

        // Create built in rules
        $builtIn = array();
        $rule = new RuleEntity();
        $rule->setSort('0');
        $rule->setTargetName('Everything');
        $rule->setTarget('Global Rule');
        $rule->setAttribute('Tax');
        $rule->setValue($this->get("helper")->getSetting("buyback_default_tax"));

        $builtIn[] = $rule;

        if($this->get("helper")->getSetting("buyback_value_minerals") == 1) {

            $rule = new RuleEntity();
            $rule->setSort('0');
            $rule->setTargetName('Anything Refinable');
            $rule->setTarget('Global Rule');
            $rule->setAttribute('Is Refined');
            $rule->setValue('Yes');

            $builtIn[] = $rule;
        }

        if($this->get("helper")->getSetting("buyback_value_salvage") == 1) {

            $rule = new RuleEntity();
            $rule->setSort('0');
            $rule->setTargetName('Anything Salvageable');
            $rule->setTarget('Global Rule');
            $rule->setAttribute('Is Refined');
            $rule->setValue('Yes');

            $builtIn[] = $rule;
        }

        return $this->render('rules/index.html.twig', array('page_name' => 'Settings', 'sub_text' => 'Buyback Rules',
            'groupform' => $groupForm->createView(), 'typeform' => $typeForm->createView(), 'rules' => $rules,
            'builtin' => $builtIn, 'testform' => $testForm->createView(), 'results' => $results));
    }

    /**
     * @Route("/admin/settings/rules/delete/{id}", name="admin_buyback_rules_delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $rule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->findOneById($id);

        if($rule != null) {

            $em = $this->getDoctrine()->getManager();

            $rules = $this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->findAllAfter($rule->getSort());

            $em->remove($rule);

            foreach($rules as $rule) {

                $rule->setSort($rule->getSort() - 1);
            }

            $em->flush();

            $this->addFlash('success', "Deleted rule");
        }
        
        return $this->redirectToRoute('admin_buyback_rules');
    }

    /**
     * @Route("/admin/settings/rules/up/{id}", name="admin_buyback_rules_up")
     */
    public function upAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $rule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->findOneById($id);

        if($rule != null) {

            $prevRule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->findOneBySort($rule->getSort() - 1);

            $prevRule->setSort($rule->getSort());
            $rule->setSort($rule->getSort() - 1);

            $em->flush();

            $this->addFlash('success', "Moved rule");
        }

        return $this->redirectToRoute('admin_buyback_rules');
    }

    /**
     * @Route("/admin/settings/rules/down/{id}", name="admin_buyback_rules_down")
     */
    public function downAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $rule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->findOneById($id);

        if($rule != null) {

            $nextRule = $this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->findOneBySort($rule->getSort() + 1);

            $nextRule->setSort($rule->getSort());
            $rule->setSort($rule->getSort() + 1);

            $em->flush();

            $this->addFlash('success', "Moved rule");
        }

        return $this->redirectToRoute('admin_buyback_rules');
    }
}
