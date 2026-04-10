<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Controller\Ops;

use App\EntityInterface\VendorTransactionInterface;
use App\Form\Ops\VendorTransactionCreateInput;
use App\Form\Ops\VendorTransactionCreateType;
use App\Form\Ops\VendorTransactionStatusUpdateInput;
use App\Form\Ops\VendorTransactionStatusUpdateType;
use App\RepositoryInterface\VendorTransactionRepositoryInterface;
use App\ServiceInterface\Ops\VendorTransactionOperatorPageBuilderInterface;
use App\ServiceInterface\VendorTransactionManagerInterface;
use App\ValueObject\VendorTransactionData;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
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
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * Render the minimal operator page for a vendor transaction contour.
     */
    #[Route('/{vendorId}', name: 'app_ops_vendor_transaction_operator_index', methods: ['GET'])]
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
    #[Route('/{vendorId}/create', name: 'app_ops_vendor_transaction_operator_create', methods: ['POST'])]
    public function create(string $vendorId, Request $request): RedirectResponse
    {
        if ($this->isTwigFormsRuntimeAvailable()) {
            if ($this->hasFlatCreatePayload($request)) {
                return $this->handleFlatCreateSubmission($vendorId, $request);
            }

            $input = new VendorTransactionCreateInput(vendorId: $vendorId);
            $form = $this->createForm(VendorTransactionCreateType::class, $input, [
                'action' => sprintf('/ops/vendor-transactions/%s/create', rawurlencode($vendorId)),
                'method' => 'POST',
                'csrf_protection' => false,
            ]);
            $this->submitFlatOrNamedForm($form, $request, ['vendorId' => $vendorId]);

            if (!$form->isSubmitted()) {
                return $this->redirectWithQuery($vendorId, 'error', 'form_invalid');
            }

            if (!$form->isValid()) {
                return $this->redirectWithQuery($vendorId, 'error', $this->firstCreateFormErrorCode($form));
            }

            try {
                $this->manager->createTransaction(new VendorTransactionData(
                    vendorId: trim($input->vendorId),
                    orderId: trim($input->orderId),
                    projectId: $this->normalizeNullableString($input->projectId),
                    amount: trim($input->amount),
                ));
            } catch (InvalidArgumentException $exception) {
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
        } catch (InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction created.');
    }

    /**
     * Handle minimal operator status update submission.
     */
    #[Route('/{vendorId}/{id}/status', name: 'app_ops_vendor_transaction_operator_update_status', methods: ['POST'])]
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
                'csrf_protection' => false,
            ]);
            $this->submitFlatOrNamedForm($form, $request, ['vendorId' => $vendorId]);

            if (!$form->isSubmitted() || !$form->isValid()) {
                return $this->redirectWithQuery($vendorId, 'error', 'form_invalid');
            }

            try {
                $this->manager->updateStatus($transaction, trim($input->status));
            } catch (InvalidArgumentException $exception) {
                return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
            }

            return $this->redirectWithQuery($vendorId, 'message', 'Transaction status updated.');
        }

        try {
            $this->manager->updateStatus($transaction, trim((string) $request->request->get('status', '')));
        } catch (InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction status updated.');
    }

    /**
     * @param list<VendorTransactionInterface> $transactions
     */
    private function renderTwigOperatorSurface(string $vendorId, array $transactions, ?string $flashMessage, ?string $errorMessage): Response
    {
        $createForm = $this->createForm(VendorTransactionCreateType::class, new VendorTransactionCreateInput(vendorId: $vendorId), [
            'action' => sprintf('/ops/vendor-transactions/%s/create', rawurlencode($vendorId)),
            'method' => 'POST',
            'csrf_protection' => false,
        ]);

        $statusForms = [];
        foreach ($transactions as $transaction) {
            $statusForms[$transaction->getId()] = $this->formFactory->createNamed(
                sprintf('status_%d', $transaction->getId()),
                VendorTransactionStatusUpdateType::class,
                new VendorTransactionStatusUpdateInput($transaction->getStatus()),
                [
                    'action' => sprintf('/ops/vendor-transactions/%s/%d/status', rawurlencode($vendorId), $transaction->getId()),
                    'method' => 'POST',
                    'csrf_protection' => false,
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

    /**
     * @param array<string, mixed> $defaults
     */
    private function submitFlatOrNamedForm(FormInterface $form, Request $request, array $defaults = []): void
    {
        $formName = $form->getName();
        $namedPayload = $request->request->all($formName);

        if ([] !== $namedPayload) {
            $form->submit($namedPayload);

            return;
        }

        $flatPayload = [];
        foreach ($form->all() as $child) {
            $childName = $child->getName();

            if ($request->request->has($childName)) {
                $flatPayload[$childName] = $request->request->get($childName);

                continue;
            }

            if (array_key_exists($childName, $defaults)) {
                $flatPayload[$childName] = $defaults[$childName];

                continue;
            }

            $flatPayload[$childName] = $child->getData();
        }

        $form->submit($flatPayload);
    }

    private function hasFlatCreatePayload(Request $request): bool
    {
        return $request->request->has('orderId')
            || $request->request->has('projectId')
            || $request->request->has('amount');
    }

    private function handleFlatCreateSubmission(string $vendorId, Request $request): RedirectResponse
    {
        $orderId = trim((string) $request->request->get('orderId', ''));
        $amount = trim((string) $request->request->get('amount', ''));

        if ('' === $orderId) {
            return $this->redirectWithQuery($vendorId, 'error', 'order_id_required');
        }

        if ('' === $amount) {
            return $this->redirectWithQuery($vendorId, 'error', 'amount_required');
        }

        try {
            $this->manager->createTransaction(new VendorTransactionData(
                vendorId: $vendorId,
                orderId: $orderId,
                projectId: $this->nullableTrimmedPostValue($request, 'projectId'),
                amount: $amount,
            ));
        } catch (InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction created.');
    }

    private function firstCreateFormErrorCode(FormInterface $form): string
    {
        if ($form->get('orderId')->isSubmitted() && !$form->get('orderId')->isValid()) {
            return 'order_id_required';
        }

        if ($form->get('amount')->isSubmitted() && !$form->get('amount')->isValid()) {
            return 'amount_required';
        }

        return 'form_invalid';
    }

    private function normalizeNullableString(?string $value): ?string
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
