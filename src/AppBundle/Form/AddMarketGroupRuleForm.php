<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;

class AddMarketGroupRuleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('marketgroupid', TypeaheadType::class, array('label' => false,
                'class' => 'EveBundle\Entity\MarketGroupsEntity', 'render' => 'marketGroupName',
                'route' => 'ajax_market_list', 'attr' => array('placeholder' => 'Market Group Name...')))
            ->add('attribute', ChoiceType::class, array(
                'choices' => array(
                    'tax' => 'Tax Percent',
                    'price' => 'Set Price',
                    'refined' => 'Is Refined',
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
            'data_class' => 'AppBundle\Model\GroupRuleModel',
        ));
    }
}

