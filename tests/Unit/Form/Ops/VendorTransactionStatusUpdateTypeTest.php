<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Ops;

use App\Form\Ops\VendorTransactionStatusUpdateInput;
use App\Form\Ops\VendorTransactionStatusUpdateType;
use App\ValueObject\VendorTransactionStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;

final class VendorTransactionStatusUpdateTypeTest extends TestCase
{
    public function testFormExposesCanonicalTransactionStatusChoices(): void
    {
        $factory = Forms::createFormFactoryBuilder()->getFormFactory();
        $form = $factory->create(VendorTransactionStatusUpdateType::class, new VendorTransactionStatusUpdateInput());

        self::assertSame(
            VendorTransactionStatus::operatorChoices(),
            $form->get('status')->getConfig()->getOption('choices')
        );
    }
}
