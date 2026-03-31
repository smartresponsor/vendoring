<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Ops;

use App\Form\Ops\VendorTransactionCreateInput;
use App\Form\Ops\VendorTransactionCreateType;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

final class VendorTransactionCreateTypeTest extends TestCase
{
    public function testFormDoesNotExposeEditableVendorIdField(): void
    {
        $factory = Forms::createFormFactoryBuilder()->getFormFactory();
        $form = $factory->create(VendorTransactionCreateType::class, new VendorTransactionCreateInput(vendorId: 'vendor-1'));

        self::assertFalse($form->has('vendorId'));
        self::assertTrue($form->has('orderId'));
        self::assertTrue($form->has('projectId'));
        self::assertTrue($form->has('amount'));
    }
}
