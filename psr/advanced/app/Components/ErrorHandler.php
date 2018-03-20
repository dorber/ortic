<?php

namespace App\Components;


use Middlewares\ErrorHandlerDefault;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class ErrorHandler extends ErrorHandlerDefault
{

    /** @var LoggerInterface */
    protected $logger;


    /**
     * ErrorHandler constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var \Throwable $error */
        $error = $request->getAttribute('error');

        if ($error->getPrevious() instanceof \Throwable && $error->getCode() >= 500 && $error->getCode() < 600) {
            $this->logger->error($error->getPrevious());
        }

        return parent::handle($request);
    }

}