<?php

declare(strict_types=1);

namespace App\Vendoring\Form\Config;

use App\Vendoring\Value\Form\Config\VendoringFeatureFlagsConfigData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class VendoringFeatureFlagsConfigFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('featureFlagsJson', TextareaType::class, [
                'label' => 'VENDORING_FEATURE_FLAGS_JSON',
                'required' => true,
                'attr' => ['rows' => 12],
            ])
            ->add('save', SubmitType::class, ['label' => 'Save pending'])
            ->add('apply', SubmitType::class, ['label' => 'Apply now', 'attr' => ['class' => 'btn btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VendoringFeatureFlagsConfigData::class,
        ]);
    }
}
