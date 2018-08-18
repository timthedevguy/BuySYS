<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;

class AddTypeRuleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('typeid', TypeaheadType::class, array('label' => false,
                'class' => 'AppBundle\Entity\SDE\TypeEntity', 'render' => 'typeName',
                'route' => 'ajax_type_list', 'attr' => array('placeholder' => 'Item Name...')))
            ->add('attribute', ChoiceType::class, array(
                'choices' => array(
                    'tax' => 'Tax Percent',
                    'price' => 'Set Price',
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
            'data_class' => 'AppBundle\Model\TypeRuleModel',
        ));
    }
}

