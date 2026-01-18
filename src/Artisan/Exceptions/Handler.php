<?php

namespace themes\Wordpress\Framework\Core\Artisan\Exceptions;

use Exception;
use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Rareloop\Lumberjack\Exceptions\Handler as LumberjackHandler;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;

class Handler extends LumberjackHandler
{
    protected $dontReport = [];

    public function report(Exception $e)
    {
        parent::report($e);
    }

    /**
     * @param ServerRequestInterface $request
     * @param Exception              $e
     *
     * @return ResponseInterface
     */
    public function render(ServerRequestInterface $request, Exception $e): ResponseInterface
    {
        (new ConsoleApplication())->renderThrowable($e, new ConsoleOutput());

        // Not ideal :(
        return new EmptyResponse();
    }
}
