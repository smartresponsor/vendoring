<?php

// Copyright (c) 2025 Oleksandr Tishchenko / Marketing America Corp

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor\Transaction\Operator;

use App\Vendoring\DTO\Ops\VendorTransactionCreateInputDTO;
use App\Vendoring\DTO\Ops\VendorTransactionStatusUpdateInputDTO;
use App\Vendoring\EntityInterface\Vendor\VendorTransactionEntityInterface;
use App\Vendoring\Form\Ops\VendorTransactionCreateForm;
use App\Vendoring\Form\Ops\VendorTransactionStatusUpdateForm;
use App\Vendoring\RepositoryInterface\Vendor\VendorTransactionRepositoryInterface;
use App\Vendoring\ServiceInterface\Transaction\VendorTransactionLifecycleServiceInterface;
use App\Vendoring\ValueObject\VendorTransactionDataValueObject;
use App\Vendoring\ValueObject\VendorTransactionStatusValueObject;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class VendorTransactionOperatorService
{
    public function __construct(
        private readonly VendorTransactionRepositoryInterface $transactions,
        private readonly VendorTransactionLifecycleServiceInterface $transactionLifecycle,
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Render the minimal operator page for a vendor transaction contour.
     */
    public function index(string $vendorId, Request $request): Response
    {
        $transactions = $this->transactions->findByVendorId($vendorId);
        $flashMessage = $this->nullableTrimmedQueryValue($request, 'message');
        $errorMessage = $this->nullableTrimmedQueryValue($request, 'error');

        return $this->renderTwigOperatorSurface($vendorId, $transactions, $flashMessage, $errorMessage);
    }

    /**
     * Handle minimal operator creation form submission.
     *
     * @throws \Throwable
     */
    public function create(string $vendorId, Request $request): RedirectResponse
    {
        if ($this->hasFlatCreatePayload($request)) {
            return $this->handleFlatCreateSubmission($vendorId, $request);
        }

        $input = new VendorTransactionCreateInputDTO(vendorId: $vendorId);
        $form = $this->formFactory->create(VendorTransactionCreateForm::class, $input, [
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
            $this->transactionLifecycle->createTransaction(new VendorTransactionDataValueObject(
                vendorId: $vendorId,
                orderId: trim($input->orderId),
                projectId: $this->normalizeNullableString($input->projectId),
                amount: trim($input->amount),
            ));
        } catch (\InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction created.');
    }

    /**
     * Handle minimal operator status update submission.
     */
    public function updateStatus(string $vendorId, int $id, Request $request): RedirectResponse
    {
        $transaction = $this->transactions->findOneByIdAndVendorId($id, $vendorId);

        if (null === $transaction) {
            return $this->redirectWithQuery($vendorId, 'error', 'not_found');
        }

        $input = new VendorTransactionStatusUpdateInputDTO($transaction->getStatus());
        $form = $this->formFactory->create(VendorTransactionStatusUpdateForm::class, $input, [
            'action' => sprintf('/ops/vendor-transactions/%s/%d/status', rawurlencode($vendorId), $id),
            'method' => 'POST',
            'csrf_protection' => false,
        ]);
        $this->submitFlatOrNamedForm($form, $request, ['vendorId' => $vendorId]);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->redirectWithQuery($vendorId, 'error', 'form_invalid');
        }

        try {
            $this->transactionLifecycle->updateStatus($transaction, trim($input->status));
        } catch (\InvalidArgumentException $exception) {
            return $this->redirectWithQuery($vendorId, 'error', $exception->getMessage());
        }

        return $this->redirectWithQuery($vendorId, 'message', 'Transaction status updated.');
    }

    /**
     * @param list<VendorTransactionEntityInterface> $transactions
     */
    private function renderTwigOperatorSurface(string $vendorId, array $transactions, ?string $flashMessage, ?string $errorMessage): Response
    {
        $template = sprintf('%s.%s', 'ops/vendor_transactions/index', 'html.twig');
        $createForm = $this->formFactory->create(VendorTransactionCreateForm::class, new VendorTransactionCreateInputDTO(vendorId: $vendorId), [
            'action' => sprintf('/ops/vendor-transactions/%s/create', rawurlencode($vendorId)),
            'method' => 'POST',
            'csrf_protection' => false,
        ]);

        $statusForms = [];
        foreach ($transactions as $transaction) {
            $statusForms[$transaction->getId()] = $this->formFactory->createNamed(
                sprintf('status_%d', $transaction->getId()),
                VendorTransactionStatusUpdateForm::class,
                new VendorTransactionStatusUpdateInputDTO($transaction->getStatus()),
                [
                    'action' => sprintf('/ops/vendor-transactions/%s/%d/status', rawurlencode($vendorId), $transaction->getId()),
                    'method' => 'POST',
                    'csrf_protection' => false,
                ],
            )->createView();
        }

        return new Response($this->twig->render($template, [
            'vendorId' => $vendorId,
            'transactions' => $transactions,
            'flashMessage' => $flashMessage,
            'errorMessage' => $errorMessage,
            'statusLabels' => VendorTransactionStatusValueObject::labels(),
            'createForm' => $createForm->createView(),
            'statusForms' => $statusForms,
        ]), 200, ['Content-Type' => 'text/html; charset=UTF-8']);
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

    /**
     * @throws \Throwable
     */
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
            $this->transactionLifecycle->createTransaction(new VendorTransactionDataValueObject(
                vendorId: $vendorId,
                orderId: $orderId,
                projectId: $this->nullableTrimmedProjectId($request),
                amount: $amount,
            ));
        } catch (\InvalidArgumentException $exception) {
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

    private function nullableTrimmedProjectId(Request $request): ?string
    {
        $value = trim((string) $request->request->get('projectId', ''));

        return '' === $value ? null : $value;
    }

    private function nullableTrimmedQueryValue(Request $request, string $key): ?string
    {
        $value = trim((string) $request->query->get($key, ''));

        return '' === $value ? null : $value;
    }
}
