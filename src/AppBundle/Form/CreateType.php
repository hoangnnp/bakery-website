<?php
namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class CreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id',HiddenType::class)
            ->add('name_vi',TextType::class,array('label'=>false))
            ->add('name_en',TextType::class,array('label'=>false))
            ->add('name_fr',TextType::class,array('label'=>false))
            ->add('price',TextType::class,array('label'=>false))
            ->add('image',TextType::class,array('label'=>false))
            ->add('description_vi',TextType::class,array('label'=>false))
            ->add('description_en',TextType::class,array('label'=>false))
            ->add('description_fr',TextType::class,array('label'=>false));
    }
}