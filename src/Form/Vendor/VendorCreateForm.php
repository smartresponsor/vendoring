<?php

declare(strict_types=1);

namespace App\Vendoring\Form\Vendor;

use App\Vendoring\Entity\Vendor\VendorEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

final class VendorCreateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('brandName', TextType::class, [
                'label' => 'Brand nameEntity',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 255),
                ],
                'attr' => ['class' => 'crud-app-input'],
            ])
            ->add('ownerUserId', IntegerType::class, [
                'label' => 'Owner user ID',
                'required' => false,
                'mapped' => false,
                'empty_data' => '',
                'constraints' => [
                    new PositiveOrZero(),
                ],
                'attr' => ['class' => 'crud-app-input', 'inputmode' => 'numeric'],
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, static function (FormEvent $event): void {
            $vendor = $event->getData();
            if (!$vendor instanceof VendorEntity) {
                return;
            }

            $form = $event->getForm();
            $brandName = trim((string) $form->get('brandName')->getData());
            if ('' !== $brandName) {
                $vendor->rename($brandName);
            }

            $ownerUserId = $form->get('ownerUserId')->getData();
            if ('' === $ownerUserId || null === $ownerUserId) {
                $vendor->changeOwnerUserId(null);

                return;
            }

            $vendor->changeOwnerUserId((int) $ownerUserId);
        });
    }

    public function configureOptions(\Symfony\Component\OptionsResolver\OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => VendorEntity::class,
            'csrf_protection' => true,
        ]);
    }
}
