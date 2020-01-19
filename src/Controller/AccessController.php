<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class AccessController {
    protected $ci;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
    }

    public function access($request, $response, $args) {
        $this->ci->get('renderer')->render($response, "access.html", $args);
    }
}
