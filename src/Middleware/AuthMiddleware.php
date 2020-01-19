<?php

namespace App\Middleware;

use Interop\Container\ContainerInterface;

class AuthMiddleware{
    protected $ci;
    protected $logger;

    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
       $this->logger = $this->ci->get('logger');
       $this->auth = $this->ci->get('auth');
    }


    public function __invoke($request, $response, $next){
        if(!$this->auth->check()){
            $args['error'] = "Vous devez etre connecté pour accéder à cette page.";
            return $response->withRedirect($this->ci->router->pathFor('auth.signin'));
        }
        return $next($request,$response);
    }

}