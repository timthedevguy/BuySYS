<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Lifo\TypeaheadBundle\Form\Type\TypeaheadType;

class ExclusionForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('marketgroupid', TypeaheadType::class, array('label' => false,
                'class' => 'EveBundle\Entity\MarketGroupsEntity', 'render' => 'marketGroupName',
                'route' => 'ajax_market_list', 'attr' => array('placeholder' => 'Market Group Name...')))
            ->add('save', SubmitType::class, array('label' => 'Add Exclusion', 'attr' => array('class' =>
            'btn btn-flat btn-success')))
        ;
    }
}