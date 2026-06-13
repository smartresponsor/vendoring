<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Form\Ops;

use App\Vendoring\DTO\Ops\VendorTransactionCreateInputDTO;
use App\Vendoring\Form\Ops\VendorTransactionCreateForm;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

final class VendorTransactionCreateFormTest extends TestCase
{
    public function testFormDoesNotExposeEditableVendorIdField(): void
    {
        $factory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
        $form = $factory->create(VendorTransactionCreateForm::class, new VendorTransactionCreateInputDTO(vendorId: 'vendor-1'));

        self::assertFalse($form->has('vendorId'));
        self::assertTrue($form->has('orderId'));
        self::assertTrue($form->has('projectId'));
        self::assertTrue($form->has('amount'));
    }
}
