<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EstimateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('items', TextareaType::class, array (
				'attr' => array(
					'placeholder' => 'Paste items from game here',
					'style' => 'height: 800px;'
				)
			))
            ->add('save', SubmitType::class, array(
            	'label' => 'Get Estimate',
				'attr' => array(
					'class' => 'form-control btn-block btn-success'
				)
			))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Model\EstimateModel',
        ));
    }
}

