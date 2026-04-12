<?php

declare(strict_types=1);

namespace App\Form\Ops;

use App\ValueObject\VendorTransactionStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

final class VendorTransactionStatusUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('status', ChoiceType::class, [
            'label' => false,
            'choices' => VendorTransactionStatus::operatorChoices(),
            'constraints' => [new NotBlank(), new Choice(choices: VendorTransactionStatus::all())],
            'attr' => ['class' => 'form-select form-select-sm'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => VendorTransactionStatusUpdateInput::class,
            'csrf_protection' => true,
        ]);
    }
}
