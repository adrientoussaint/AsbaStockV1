<?php

namespace App\Controller\Auth;

use Interop\Container\ContainerInterface;

class AuthController {
    protected $ci;
    protected $logger;

    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
       $this->logger = $this->ci->get('logger');
       $this->auth = $this->ci->get('auth');
    }

    public function getSignOut($request, $response, $args){
        $this->auth->logout();
        return $response->withRedirect($this->ci->router->pathFor('auth.signin'));
    }

    public function getSignIn($request, $response, $args){
        $this->ci->view->render($response, "auth/signin.html", $args);
    }
    
    public function postSignIn($request, $response, $args){
        $password = $request->getParam('password');
        $username = $request->getParam('username');
        
        
        $auth =  $this->ci->auth->attempt($username,$password);
        
        if(!$auth){
            $args['error'] = 'Nom d\'utilisateur ou Mot de passe éronné';
            $this->ci->view->render($response, "auth/signin.html", $args);
        };

        return $response->withRedirect($this->ci->router->pathFor('home'));
    }

    public function getSignUp($request, $response, $args){
        $this->ci->view->render($response, "auth/signup.html", $args);
    }
    
    public function postSignUp($request, $response, $args){
        $postData = $request->getParsedBody();
        $username = htmlspecialchars($postData['username']);
        $password = htmlspecialchars($postData['password']);
        $name = htmlspecialchars($postData['name']);
        $mail = htmlspecialchars($postData['mail']);


        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $user = new \App\Model\User;
        $user->name = $name;
        $user->mail = $mail;
        $user->username = $username;
        $user->password = $hash;
        $user->save();
        $args["signup"] = "Utilisateur ajouté !";
        $this->ci->view->render($response, "auth/signin.html", $args); 
    }

    // public function login($request, $response, $args) {
    //     $this->ci->get('renderer')->render($response, "login.html", $args);
    // }

    // public function home($request, $response, $args) {
    //     $this->ci->get('renderer')->render($response, "home.html", $args);
    // }

    // public function connection($request, $response, $args) {
    //         $postData = $request->getParsedBody();
    //         $username = htmlspecialchars($postData['username']);
    //         $password = htmlspecialchars($postData['password']);
    //         $hash = password_hash($password, PASSWORD_DEFAULT);
    //         $this->logger->info($hash);
    //         $user = \App\Model\User::select()->where(['username'=> $username, 'password'=> $password])->first();
    //         if (count($user) == 0) {
    //             // add the message to the request for the showLogin function to assign
    //             $_SESSION['USER'] = null;
    //             $args['loginError']="Nom d'utilisateur ou mot de passe éroné";
    //             // return the login view
    //             return $this->login($request,$response,$args);
    //         }else{
    //             session_start();
    //             $_SESSION['USER'] = $user;
    //             $args['username']=$user->name;
    //             return $this->home($request,$response,$args);
    //         }
    //         $this->ci->get('renderer')->render($response, "admin.html", $args);        
    //     }
        
    // public function admin($request, $response, $args) {
    //     if(isset($_SESSION['USER']) && !empty($_SESSION['USER'])){
    //         $user = $_SESSION['USER'];
    //         $args['username']= $user->user;


    //         $voitures = \App\Model\Voiture::all();
    //         $args['voitures'] = $voitures;
    //         $this->ci->get('renderer')->render($response, "admin.html", $args);
    //     }else{
    //         return $this->login($request,$response,$args);
    //     }
    // }
    // public function logout($request, $response, $args) {
    //     $_SESSION['USER']=null;
    //     $this->ci->get('renderer')->render($response, "login.html", $args);
    // }
}
