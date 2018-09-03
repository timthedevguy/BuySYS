<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;

class AddGroupRuleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('groupid', TypeaheadType::class, array('label' => false,
                'class' => 'AppBundle\Entity\SDE\GroupsEntity', 'render' => 'groupName',
                'route' => 'ajax_group_list', 'attr' => array('placeholder' => 'Group Name...')))
            ->add('attribute', ChoiceType::class, array(
                'choices' => array(
					'Tax Percent' => 'tax',
					'Set Price' => 'price',
					'Is Refined' => 'isrefined',
					'Can Buy' => 'canbuy'
                )
            ))
            ->add('value', TextType::class)
            ->add('save', SubmitType::class, array('label' => 'Add Rule', 'attr' => array('class' =>
                'btn btn-flat btn-success')))
        ;
    }
    
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Model\GroupRuleModel',
        ));
    }
}

