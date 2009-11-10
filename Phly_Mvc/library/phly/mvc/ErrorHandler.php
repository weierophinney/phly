<?php
namespace phly\mvc;

class ErrorHandler implements ErrorHandlerInterface
{
    public function handle(EventInterface $e)
    {
        $response   = $e->getResponse();
        $exceptions = $e->getExceptions();
        $exception  = array_pop($exceptions);
        $response->assign(array(
            'exception' => $exception,
        ), 'error/error');

        switch (get_class($exception)) {
            case ($exception instanceof PageNotFoundException):
                $response->setCode(404);
                $response->assign('message', 'Page Not Found', 'error/error');
                break;
            default:
                $response->setCode(500);
                $response->assign('message', 'An unexpected error occurred', 'error/error');
                break;
        }
    }
}
