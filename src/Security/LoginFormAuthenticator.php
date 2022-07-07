<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    private const APP_DEFAULT = 'app_default';

    private const ADMIN_DEFAULT = 'admin_default';

    private UrlGeneratorInterface $urlGenerator;

    private UserRepository $repository;

    private ?User $user = null;

    private Security $security;

    public function __construct(UrlGeneratorInterface $urlGenerator, UserRepository $repository, Security $security)
    {
        $this->urlGenerator = $urlGenerator;
        $this->repository = $repository;
        $this->security = $security;
    }

    public function authenticate(Request $request): Passport
    {
        $username = $request->request->get('username', '');

        $this->user = $this->repository->findOneBy(compact('username'));

        $request->getSession()->set(Security::LAST_USERNAME, $username);

        return new Passport(
            new UserBadge($username),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        $route = self::APP_DEFAULT;

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $route = self::ADMIN_DEFAULT;
        }

        // For example:
        return new RedirectResponse($this->urlGenerator->generate($route));
        // throw new \Exception('TODO: provide a valid redirect inside ' . __FILE__);
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function getUser(): ?User
    {
        return $this->user;
    }
}
