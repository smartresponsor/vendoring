<?php

declare(strict_types=1);

namespace App\Vendoring\Form\Ops;

use App\Vendoring\DTO\Ops\VendorTransactionStatusUpdateInputDTO;
use App\Vendoring\ValueObject\VendorTransactionStatusValueObject;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

final class VendorTransactionStatusUpdateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('status', ChoiceType::class, [
            'label' => false,
            'choices' => VendorTransactionStatusValueObject::operatorChoices(),
            'constraints' => [new NotBlank(), new Choice(choices: VendorTransactionStatusValueObject::all())],
            'attr' => ['class' => 'form-select form-select-sm'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);
        $resolver->setDefaults([
            'data_class' => VendorTransactionStatusUpdateInputDTO::class,
            'csrf_protection' => true,
        ]);
    }
}
