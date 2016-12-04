<?php
namespace AppBundle\Controller;

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
        $groupModel = new GroupRuleModel();
        $typeModel = new TypeRuleModel();

        $groupForm = $this->createForm(AddGroupRuleForm::class, $groupModel);
        $typeForm = $this->createForm(AddTypeRuleForm::class, $typeModel);

        $em = $this->getDoctrine()->getManager();
        $rules = $em->getRepository('AppBundle:RuleEntity', 'default')->findAll();

        if($request->getMethod() == "POST") {

            $form_results = $request->request->get('add_type_rule_form');
            $rule = new RuleEntity();

            if($form_results == null) {

                // Submitted form is Group Form
                $form_results = $request->request->get('add_group_rule_form');
                $rule->setTarget('group');
                $rule->setTargetId($form_results['marketgroupid']);
                $rule->setTargetName($this->getDoctrine()->getRepository('EveBundle:MarketGroupsEntity', 'evedata')->
                findOneByMarketGroupID($form_results['marketgroupid'])->getMarketGroupName());
            } else {

                // Submitted form was Type Form
                $rule->setTarget('type');
                $rule->setTargetId($form_results['typeid']);
                $rule->setTargetName($this->getDoctrine()->getRepository('EveBundle:TypeEntity', 'evedata')->
                findOneByTypeID($form_results['typeid'])->getTypeName());
            }

            $rule->setSort($this->getDoctrine()->getRepository('AppBundle:RuleEntity', 'default')->getNextSort());
            $rule->setAttribute($form_results['attribute']);
            $rule->setValue($form_results['value']);
            $em->persist($rule);
            $em->flush();
        }

        return $this->render('rules/index.html.twig', array('page_name' => 'Settings', 'sub_text' => 'Buyback Rules',
            'groupform' => $groupForm->createView(), 'typeform' => $typeForm->createView(), 'rules' => $rules));
    }
}
