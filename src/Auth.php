<?php 

namespace App;

use \App\Model\User;

class Auth{
    public function attempt($username, $password){
            $user = \App\Model\User::select()->where('username', $username)->first();
            if (!$user) {
                // add the message to the request for the showLogin function to assign
                $_SESSION['USER'] = null;
                $args['error']="Nom d'utilisateur ou mot de passe éroné";
                // return the login view
                return false;
            }

            if(password_verify($password, $user->password)){
                $_SESSION['USER'] = $user->id;
                return true;
            }
    }

    public function check(){
        return isset($_SESSION['USER']);
    }

    
    public function user(){
        return \App\Model\User::select()->where('id', $_SESSION['USER'])->first();
    }

    public function logout(){
        unset($_SESSION['USER']);
    }
}
