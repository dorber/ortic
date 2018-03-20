<?php

namespace App\Middlewares;


use App\Data\Repositories\UsersRepository;
use DI\FactoryInterface;
use Middlewares\HttpErrorException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use PSR7Sessions\Storageless\Http\SessionMiddleware;
use PSR7Sessions\Storageless\Session\SessionInterface;

class AuthMiddleware implements MiddlewareInterface
{

    /** @var ContainerInterface|FactoryInterface */
    protected $container;


    /**
     * AuthMiddleware constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        if ($session instanceof SessionInterface && $userId = $session->get('userId')) {
            $usersRepository = $this->container->get(UsersRepository::class);
            $user = $usersRepository->getById($session->get('userId'));

            if ($user) {
                return $handler->handle($request->withAttribute('user', $user));
            }
        }

        return $this->container->make(ResponseInterface::class)->withRedirect('/', 302);
    }

}