<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddRoleRuleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', ChoiceType::class, array(
                'choices' => array(
                    'ROLE_SYSTEM_ADMIN' => 'ROLE_SYSTEM_ADMIN',
                    'ROLE_TRANSACTION_ADMIN' => 'SROLE_TRANSACTION_ADMIN',
                    'ROLE_BUY_ADMIN' => 'ROLE_BUY_ADMIN',
                    'ROLE_SELL_ADMIN' => 'ROLE_SELL_ADMIN',
                    'ROLE_SRP_ADMIN' => 'ROLE_SRP_ADMIN',
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_EDITOR' => 'ROLE_EDITOR',
                    'ROLE_MEMBER' => 'ROLE_MEMBER',
                    'ROLE_ALLY' => 'ROLE_ALLY',
                    'ROLE_GUEST' => 'ROLE_GUEST'
                )
            ))
            ->add('attribute', ChoiceType::class, array(
                'choices' => array(
                    'tax' => 'Tax Percent',
                    'isrefined' => 'Is Refined',
                    'canbuy' => 'Can Buy'
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
            'data_class' => 'AppBundle\Model\RoleRuleModel',
        ));
    }
}

