<?php

declare(strict_types=1);

namespace App\Form\Ops;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class VendorTransactionCreateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vendorId', TextType::class, [
                'label' => 'Vendor ID',
                'constraints' => [new NotBlank(), new Length(max: 120)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('orderId', TextType::class, [
                'label' => 'Order ID',
                'constraints' => [new NotBlank(), new Length(max: 120)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('projectId', TextType::class, [
                'label' => 'Project ID',
                'required' => false,
                'empty_data' => '',
                'constraints' => [new Length(max: 120)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('amount', MoneyType::class, [
                'label' => 'Amount',
                'currency' => 'USD',
                'divisor' => 1,
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control', 'inputmode' => 'decimal'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VendorTransactionCreateInput::class,
            'csrf_protection' => true,
        ]);
    }
}
