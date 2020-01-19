<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class HomeController {
    protected $ci;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
    }

    public function home($request, $response, $args) {
        $this->ci->view->render($response, "home.html", $args);
    }

    public function newsletterSubscribed($request, $response, $args) {
        $pattern =  '/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/';
        $postData = $request->getParsedBody();
        $email = htmlspecialchars($postData['email']);
        $existingmail = \App\Model\Newsletter::select()->where('email', $email)->get();
       if(preg_match($pattern, $email)){
           if(count($existingmail) > 0){
                echo "Vous êtes déjà inscrit à notre newsletter";
           }else{
               $new = new \App\Model\Newsletter;
               $new->email = $email;
               $new->save();
               echo "Vous recevrez notre newsletter !";
           }
       }else{
            echo "Erreur - Email incorrect";
       }    
    }
}
