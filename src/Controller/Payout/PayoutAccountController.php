<?php

declare(strict_types=1);

namespace App\Controller\Payout;

use App\Entity\Vendor\Payout\PayoutAccount;
use App\RepositoryInterface\Payout\PayoutAccountRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/payouts/account')]
final class PayoutAccountController extends AbstractController
{
    public function __construct(private readonly PayoutAccountRepositoryInterface $repo)
    {
    }

    #[Route('', methods: ['POST'])]
    public function upsert(Request $r): JsonResponse
    {
        $p = $r->toArray();
        foreach (['tenantId', 'vendorId', 'provider', 'accountRef', 'currency'] as $k) {
            if (!isset($p[$k])) {
                return new JsonResponse(['error' => "$k required"], 422);
            }
        }
        $a = new PayoutAccount(Uuid::v4()->toRfc4122(), (string) $p['tenantId'], (string) $p['vendorId'], (string) $p['provider'], (string) $p['accountRef'], (string) $p['currency'], (bool) ($p['active'] ?? true), (new \DateTimeImmutable())->format('Y-m-d H:i:s'));
        $this->repo->upsert($a);

        return new JsonResponse(['data' => ['provider' => $a->provider, 'accountRef' => $a->accountRef, 'active' => $a->active]], 200);
    }
}
