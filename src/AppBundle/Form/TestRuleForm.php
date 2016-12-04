<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;

class TestRuleForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('typeid', TypeaheadType::class, array('label' => false,
                'class' => 'EveBundle\Entity\TypeEntity', 'render' => 'typeName',
                'route' => 'ajax_type_list', 'attr' => array('placeholder' => 'Item Name...')))
            ->add('save', SubmitType::class, array(
                'label' => 'Test Rules',
                'attr' => array(
                    'class' => 'btn btn-primary'
                )
            ))
        ;
    }

    /*public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity',
        ));
    }*/
}

