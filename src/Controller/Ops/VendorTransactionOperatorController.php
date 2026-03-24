<?php
# Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Ops;

use App\Form\Ops\VendorTransactionCreateInput;
use App\Form\Ops\VendorTransactionCreateType;
use App\Form\Ops\VendorTransactionStatusUpdateInput;
use App\Form\Ops\VendorTransactionStatusUpdateType;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\Ops\VendorTransactionOperatorPageBuilderInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\VendorTransactionData;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ops/vendor-transactions')]
final class VendorTransactionOperatorController extends AbstractController
{
    public function __construct(
        private readonly VendorTransactionRepositoryInterface $transactions,
        private readonly VendorTransactionManagerInterface $manager,
        private readonly VendorTransactionOperatorPageBuilderInterface $pageBuilder,
    ) {
    }

    /**
     * Render the minimal operator page for a vendor transaction contour.
     */
    #[Route('/{vendorId}', methods: ['GET'])]
    public function index(string $vendorId, Request $request): Response
    {
        $transactions = $this->transactions->findByVendorId($vendorId);
        $flashMessage = $this->nullableTrimmedQueryValue($request, 'message');
        $errorMessage = $this->nullableTrimmedQueryValue($request, 'error');

        if ($this->isTwigFormsRuntimeAvailable()) {
            return $this->renderTwigOperatorSurface($vendorId, $transactions, $flashMessage, $errorMessage);
        }

        $html = $this->pageBuilder->renderIndex($vendorId, $transactions, $flashMessage, $errorMessage);

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    /**
     * Handle minimal operator creation form submission.
     */
    #[Route('/{vendorId}/create', methods: ['POST'])]
    public function create(string $vendorId, Request $request): RedirectResponse
    {
        if ($this->isTwigFormsRuntimeAvailable()) {
            $input = new VendorTransactionCreateInput(vendorId: $vendorId);
            $form = $this->createForm(VendorTransactionCreateType::class, $input, [
                'action' => sprintf('/ops/vendor-transactions/%s/create', rawurlencode($vendorId)),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->redirectWithQuery($vendorId, 'error', 'form_invalid');
            }

            try {
                $this->manager->createTransaction(new VendorTransactionData(
                    vendorId: trim($input->vendorId),
                    orderId: trim($input->orderId),
                    projectId: $this->nullableString($input->projectId),
                    amount: trim($input->amount),
                ));
            } catch (\InvalidArgumentException $exception) {
                return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
            }

            return $this->redirectWithQuery($vendorId, 'message', 'Transaction created.');
        }

        try {
            $this->manager->createTransaction(new VendorTransactionData(
                vendorId: $vendorId,
                orderId: trim((string) $request->request->get('orderId', '')),
                projectId: $this->nullableTrimmedPostValue($request, 'projectId'),
                amount: trim((string) $request->request->get('amount', '')),
            ));
        } catch (\InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction created.');
    }

    /**
     * Handle minimal operator status update submission.
     */
    #[Route('/{vendorId}/{id}/status', methods: ['POST'])]
    public function updateStatus(string $vendorId, int $id, Request $request): RedirectResponse
    {
        $transaction = $this->transactions->findOneByIdAndVendorId($id, $vendorId);

        if (null === $transaction) {
            return $this->redirectWithQuery($vendorId, 'error', 'not_found');
        }

        if ($this->isTwigFormsRuntimeAvailable()) {
            $input = new VendorTransactionStatusUpdateInput($transaction->getStatus());
            $form = $this->createForm(VendorTransactionStatusUpdateType::class, $input, [
                'action' => sprintf('/ops/vendor-transactions/%s/%d/status', rawurlencode($vendorId), $id),
                'method' => 'POST',
            ]);
            $form->handleRequest($request);

            if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->redirectWithQuery($vendorId, 'error', 'form_invalid');
            }

            try {
                $this->manager->updateStatus($transaction, trim($input->status));
            } catch (\InvalidArgumentException $exception) {
                return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
            }

            return $this->redirectWithQuery($vendorId, 'message', 'Transaction status updated.');
        }

        try {
            $this->manager->updateStatus($transaction, trim((string) $request->request->get('status', '')));
        } catch (\InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction status updated.');
    }

    private function renderTwigOperatorSurface(string $vendorId, array $transactions, ?string $flashMessage, ?string $errorMessage): Response
    {
        $createForm = $this->createForm(VendorTransactionCreateType::class, new VendorTransactionCreateInput(vendorId: $vendorId), [
            'action' => sprintf('/ops/vendor-transactions/%s/create', rawurlencode($vendorId)),
            'method' => 'POST',
        ]);

        $statusForms = [];
        foreach ($transactions as $transaction) {
            $statusForms[$transaction->getId()] = $this->createNamed(
                sprintf('status_%d', $transaction->getId()),
                VendorTransactionStatusUpdateType::class,
                new VendorTransactionStatusUpdateInput($transaction->getStatus()),
                [
                    'action' => sprintf('/ops/vendor-transactions/%s/%d/status', rawurlencode($vendorId), $transaction->getId()),
                    'method' => 'POST',
                ],
            )->createView();
        }

        return $this->render('ops/vendor_transactions/index.html.twig', [
            'vendorId' => $vendorId,
            'transactions' => $transactions,
            'flashMessage' => $flashMessage,
            'errorMessage' => $errorMessage,
            'createForm' => $createForm->createView(),
            'statusForms' => $statusForms,
        ]);
    }

    private function isTwigFormsRuntimeAvailable(): bool
    {
        return $this->container->has('twig') && $this->container->has('form.factory');
    }

    private function nullableString(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $trimmed;
    }

    private function redirectWithQuery(string $vendorId, string $key, string $value): RedirectResponse
    {
        return new RedirectResponse(sprintf('/ops/vendor-transactions/%s?%s=%s', rawurlencode($vendorId), rawurlencode($key), rawurlencode($value)));
    }

    private function nullableTrimmedPostValue(Request $request, string $key): ?string
    {
        $value = trim((string) $request->request->get($key, ''));

        return '' === $value ? null : $value;
    }

    private function nullableTrimmedQueryValue(Request $request, string $key): ?string
    {
        $value = trim((string) $request->query->get($key, ''));

        return '' === $value ? null : $value;
    }
}
