<?php

declare(strict_types=1);
/*
 * Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp
 * Author: Oleksandr Tishchenko <dev@highhopesamerica.com>
 * Owner: Marketing America Corp
 */

namespace App\Tests\Command;

use App\Command\CategoryReviewAssignCommand;
use App\Policy\CategoryReviewAssignmentPolicy;
use App\Repository\VendorCatalogCategoryChangeRequestRepository;
use App\Repository\VendorCatalogReviewAssignmentRepository;
use App\Service\CatalogReviewAssignmentService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class CategoryReviewAssignCommandTest extends TestCase
{
    public function testExecutePrintsAssignmentPayload(): void
    {
        $requestRepository = new VendorCatalogCategoryChangeRequestRepository();
        $requestRepository->save(\App\Entity\VendorCatalogCategoryChangeRequest::open('req-100', 'cat-100', 'submitter-1', 'Promote category', ['title' => 'Garden']));

        $service = new CatalogReviewAssignmentService(
            $requestRepository,
            new VendorCatalogReviewAssignmentRepository(),
            new CategoryReviewAssignmentPolicy(),
        );

        $command = new CategoryReviewAssignCommand($service);
        $tester = new CommandTester($command);
        $exitCode = $tester->execute([
            'requestId' => 'req-100',
            'reviewer' => 'reviewer-1',
            'assignedBy' => 'ops.user',
            '--priority' => 'high',
        ]);

        self::assertSame(0, $exitCode);

        $payload = json_decode(trim($tester->getDisplay()), true, 512, JSON_THROW_ON_ERROR);
        self::assertIsArray($payload);
        self::assertSame('req-100', $payload['requestId'] ?? null);
        self::assertSame('cat-100', $payload['categoryId'] ?? null);
        self::assertSame('reviewer-1', $payload['assignedReviewer'] ?? null);
        self::assertSame('high', $payload['priority'] ?? null);
    }
}
