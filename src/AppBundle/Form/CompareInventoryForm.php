<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CompareInventoryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', TextareaType::class, array('label' => false, 'attr' => array('placeholder' => 'Paste items from game here...', 'rows' => '10')))
            ->add('submit', SubmitType::class, array('label' => 'Compare', 'attr' => array('class' => 'btn btn-primary pull-right')))
        ;
    }
}