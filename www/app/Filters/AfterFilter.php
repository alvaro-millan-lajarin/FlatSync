<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AfterFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Code to run before controller
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Code to run after controller
        $response->setBody($response->getBody() . ' AFTER');
    }
}