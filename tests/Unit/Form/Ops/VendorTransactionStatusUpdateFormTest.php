<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Form\Ops;

use App\Vendoring\DTO\Ops\VendorTransactionStatusUpdateInputDTO;
use App\Vendoring\Form\Ops\VendorTransactionStatusUpdateForm;
use App\Vendoring\ValueObject\VendorTransactionStatusValueObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

final class VendorTransactionStatusUpdateFormTest extends TestCase
{
    public function testFormExposesCanonicalTransactionStatusChoices(): void
    {
        $factory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
        $form = $factory->create(VendorTransactionStatusUpdateForm::class, new VendorTransactionStatusUpdateInputDTO());

        self::assertSame(
            VendorTransactionStatusValueObject::operatorChoices(),
            $form->get('status')->getConfig()->getOption('choices'),
        );
    }
}
