<?php

declare(strict_types=1);

namespace App\Vendoring\Authenticator;

use App\Vendoring\Security\VendorTransactionMachineRequestMatcher;
use App\Vendoring\ServiceInterface\Security\VendorApiKeyServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\InMemoryUser;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Stateless machine authentication. No Accessing user or human role is created.
 * The transaction service independently revalidates the unchanged bearer,
 * its operation permission, active vendor identity and persisted ownership.
 */
final class VendorTransactionMachineAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly VendorTransactionMachineRequestMatcher $matcher,
        private readonly VendorApiKeyServiceInterface $apiKeys,
    ) {
    }

    public function supports(Request $request): bool
    {
        return $this->matcher->matches($request) && $request->headers->has('Authorization');
    }

    public function authenticate(Request $request): SelfValidatingPassport
    {
        $authorization = trim((string) $request->headers->get('Authorization', ''));
        if (1 !== preg_match('/^Bearer\s+\S+$/iD', $authorization)) {
            throw new CustomUserMessageAuthenticationException('invalid_api_token');
        }

        $vendor = $this->apiKeys->resolveVendorFromAuthHeader($authorization);
        $id = $vendor?->getId();
        if (null === $id || $id < 1) {
            throw new CustomUserMessageAuthenticationException('invalid_api_token');
        }

        if ('active' !== $vendor->getStatus()) {
            throw new CustomUserMessageAuthenticationException('vendor_inactive');
        }

        // The original header remains intact for the domain-level permission check.
        $identifier = 'vendoring-machine:'.$id;

        return new SelfValidatingPassport(new UserBadge(
            $identifier,
            static fn (): InMemoryUser => new InMemoryUser($identifier, null, ['ROLE_VENDOR_MACHINE']),
        ));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($exception instanceof CustomUserMessageAuthenticationException && 'vendor_inactive' === $exception->getMessageKey()) {
            $permission = $request->isMethod('GET') ? 'read:transactions' : 'write:transactions';

            return new JsonResponse(['error' => 'permission_denied', 'requiredPermission' => $permission], 403, [
                'X-API-Version' => '1',
                'X-Auth-Required-Permission' => $permission,
            ]);
        }

        return $this->unauthorized($request, '' === trim((string) $request->headers->get('Authorization', '')) ? 'authentication_required' : 'invalid_api_token');
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return $this->unauthorized($request, 'authentication_required');
    }

    private function unauthorized(Request $request, string $error): JsonResponse
    {
        $permission = $request->isMethod('GET') ? 'read:transactions' : 'write:transactions';

        return new JsonResponse(['error' => $error, 'requiredPermission' => $permission], 401, [
            'WWW-Authenticate' => 'Bearer',
            'X-API-Version' => '1',
            'X-Auth-Required-Permission' => $permission,
        ]);
    }
}
