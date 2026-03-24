<?php

declare(strict_types=1);

namespace App\Form\Ops;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;

final class VendorTransactionStatusUpdateType extends AbstractType
{
    /**
     * @var array<string, string>
     */
    private const STATUSES = [
        'Pending' => 'pending',
        'Authorized' => 'authorized',
        'Captured' => 'captured',
        'Failed' => 'failed',
        'Refunded' => 'refunded',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
            'label' => false,
            'choices' => self::STATUSES,
            'constraints' => [new NotBlank(), new Choice(choices: array_values(self::STATUSES))],
            'attr' => ['class' => 'form-select form-select-sm'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => VendorTransactionStatusUpdateInput::class,
            'csrf_protection' => true,
        ]);
    }
}
