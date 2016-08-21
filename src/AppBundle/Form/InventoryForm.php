<?php
namespace AppBundle\Form;

use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class InventoryForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('typeid', TypeaheadType::class, array('label' => 'Name', 'class' => 'EveBundle\Entity\TypeEntity', 'render' => 'typeName', 'route' => 'ajax_type_list'))
            ->add('quantity', TextType::class)
            ->add('cost', TextType::class, array('label' => 'Manufacturing Cost'))
            ->add('save', SubmitType::class)
        ;
    }
}