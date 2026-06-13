<?php

declare(strict_types=1);

namespace App\Vendoring\Service\Http\Vendor;

use App\Cruding\Dto\Crud\Entrypoint\CrudEntrypointContext;
use App\Cruding\Value\Surface\CrudSurfaceContract;

final readonly class VendorHttpRouteResponseService
{
    public function read(
        CrudEntrypointContext $context,
        string $resourcePath,
        string $operation,
        string $title,
        mixed $data = null,
    ): CrudSurfaceContract {
        return $this->contract(
            $context,
            $resourcePath,
            $operation,
            'read_route_ready',
            $title,
            false,
            'index' === $operation ? 'collection' : 'item',
            $data,
        );
    }

    public function mutation(
        CrudEntrypointContext $context,
        string $resourcePath,
        string $operation,
        string $title,
        mixed $data,
    ): CrudSurfaceContract {
        return $this->contract(
            $context,
            $resourcePath,
            $operation,
            'mutation_completed',
            $title,
            true,
            'item',
            $data,
        );
    }

    public function blocked(
        CrudEntrypointContext $context,
        string $resourcePath,
        string $operation,
        string $title,
    ): CrudSurfaceContract {
        return $this->contract(
            $context,
            $resourcePath,
            $operation,
            'route_blocked',
            $title,
            false,
            'blocked',
            null,
        );
    }

    private function contract(
        CrudEntrypointContext $context,
        string $resourcePath,
        string $operation,
        string $status,
        string $title,
        bool $mutationAllowed,
        string $view,
        mixed $data,
    ): CrudSurfaceContract {
        $routeContext = [
            'surface' => 'vendor',
            'resourcePath' => $resourcePath,
            'resourceLabel' => $this->label($resourcePath),
            'operation' => $operation,
            'identifierField' => $context->crudContext->identifierField,
            'identifierValue' => $context->crudContext->identifierValue,
            'formTypeClass' => $context->crudContext->formTypeClass,
            'resolver' => $context->crudContext->identifierField,
            'entrypointPattern' => 'Vendor*Service',
            'controllerAllowed' => false,
        ];

        $meta = [
            'title' => $title,
            'status' => $status,
            'format' => 'auto',
            'component' => 'Vendoring',
            'sourceComponent' => 'Vendoring',
            'persistence' => 'active',
            'mutationAllowed' => $mutationAllowed,
            'controllerAllowed' => false,
            'crudingContract' => $this->crudingContract($resourcePath, $operation, $mutationAllowed),
            'viewingContract' => $this->viewingContract($resourcePath, $operation, $view),
            'interfacingDependency' => 'not-bound-inside-vendoring',
            'data' => $data,
        ];

        $locations = [
            'body' => [
                [
                    'type' => 'vendor-resource',
                    'resource' => $resourcePath,
                    'operation' => $operation,
                    'status' => $status,
                    'data' => $data,
                ],
            ],
            'diagnostic' => [
                [
                    'type' => 'vendor-route-contract',
                    'cruding' => $meta['crudingContract'],
                    'viewing' => $meta['viewingContract'],
                    'interfacing' => $meta['interfacingDependency'],
                ],
            ],
        ];

        return CrudSurfaceContract::forSurface(
            view: $view,
            routeContext: $routeContext,
            locations: $locations,
            meta: $meta,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function crudingContract(string $resourcePath, string $operation, bool $mutationAllowed): array
    {
        return [
            'grammar' => 'id-last',
            'componentRole' => 'subject-entrypoint',
            'resource' => $resourcePath,
            'operation' => $operation,
            'resolver' => 'id',
            'mutationAllowed' => $mutationAllowed,
            'controllerAllowed' => false,
            'entrypointPattern' => 'Vendor*Service',
            'businessService' => 'VendorCrudService',
            'result' => 'CrudSurfaceContract',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function viewingContract(string $resourcePath, string $operation, string $view): array
    {
        return [
            'surface' => $view,
            'resource' => $resourcePath,
            'operation' => $operation,
            'readModel' => 'VendorEntity',
            'templateCandidate' => $this->templateCandidate($resourcePath, $operation),
            'normalization' => 'surface-renderable-object',
            'interfacingBridge' => 'not-bound-inside-vendoring',
        ];
    }

    private function templateCandidate(string $resourcePath, string $operation): ?string
    {
        if ('vendor' !== $resourcePath) {
            return null;
        }

        return match ($operation) {
            'index' => 'vendor/index.html.twig',
            'show' => 'vendor/show.html.twig',
            'new', 'edit' => 'vendor/form.html.twig',
            default => null,
        };
    }

    private function label(string $resourcePath): string
    {
        return ucwords(str_replace('/', ' ', $resourcePath));
    }
}
