<?php
namespace AppBundle\Controller;

use AppBundle\Form\AddMarketGroupRuleForm;
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
     * @Route("/system/admin/settings/rules", name="admin_buyback_rules")
     */
    public function indexAction(Request $request)
    {
        $results = null;
        //$groupModel = new GroupRuleModel();
        //$typeModel = new TypeRuleModel();

        $groupForm = $this->createForm(AddGroupRuleForm::class);
        $marketGroupForm = $this->createForm(AddMarketGroupRuleForm::class);
        $typeForm = $this->createForm(AddTypeRuleForm::class);
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

                    if($form_results == null) {

                        // Submited Form is MarketGroup Form
                        $form_results = $request->request->get('add_marketgroup_rule_form');
                        $rule->setTarget('marketgroup');
                        $rule->setTargetId($form_results['marketgroupid']);
                        $rule->setTargetName($this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity', 'evedata')->
                        findOneByMarketGroupID($form_results['marketgroupid'])->getMarketGroupName());

                        $this->addFlash('success', "Added new Market Group rule");
                    } else {

                        // Submited Form is Group Form
                        $rule->setTarget('group');
                        $rule->setTargetId($form_results['groupid']);
                        $rule->setTargetName($this->getDoctrine()->getRepository('EveBundle:GroupsEntity', 'evedata')->
                        findOneByGroupID($form_results['groupid'])->getGroupName());

                        $this->addFlash('success', "Added new Group rule");
                    }

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

                $results = $this->get('market')->getMergedBuybackRuleForType($form_results['typeid']);
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
            'builtin' => $builtIn, 'testform' => $testForm->createView(), 'marketgroupform' => $marketGroupForm->createView(),
            'results' => $results));
    }

    /**
     * @Route("/system/admin/settings/rules/delete/{id}", name="admin_buyback_rules_delete")
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
     * @Route("/system/admin/settings/rules/up/{id}", name="admin_buyback_rules_up")
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
     * @Route("/system/admin/settings/rules/down/{id}", name="admin_buyback_rules_down")
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
