<?php

declare(strict_types=1);

namespace App\Vendoring\Tests\Unit\Form\Ops;

use App\Vendoring\Form\Ops\VendorTransactionStatusUpdateInput;
use App\Vendoring\Form\Ops\VendorTransactionStatusUpdateType;
use App\Vendoring\ValueObject\VendorTransactionStatus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Validator\Validation;

final class VendorTransactionStatusUpdateTypeTest extends TestCase
{
    public function testFormExposesCanonicalTransactionStatusChoices(): void
    {
        $factory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->getFormFactory();
        $form = $factory->create(VendorTransactionStatusUpdateType::class, new VendorTransactionStatusUpdateInput());

        self::assertSame(
            VendorTransactionStatus::operatorChoices(),
            $form->get('status')->getConfig()->getOption('choices'),
        );
    }
}
