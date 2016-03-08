<?php

namespace Sentinnel\CoreBundle\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LogsFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', 'choice', array(
                'required' => false,
                'choices' => array(
                    'ERROR' => 'Error',
                    'CRITICAL' => 'Critical',
                    'WARNING' => 'Warning',
                    'INFO' => 'Info',
                    'DEBUG' => 'Debug',
                )
            ))
            ->add('date', 'date', array(
                'required' => false,
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
    }

    public function getName()
    {
        return 'sentinnel_logs_filter';
    }

}
