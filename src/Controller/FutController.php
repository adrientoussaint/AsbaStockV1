<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class FutController {
    protected $ci;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
    }

    public function fut($request, $response, $args) {
        $futs = \App\Model\listFut::all();
        $tirants = \App\Model\Tirant::all();
        
        $args['futs'] = $futs;
        $args['tirants'] = $tirants;
        $this->ci->view->render($response, "fut.html", $args);
    }

    public function changeTirantLength($request, $response, $args){

        $postData = $request->getParsedBody();
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        
        $gamme = $args['gamme'];

        $fut = \App\Model\listFut::select()->where('id', $id)->first();
        if($gamme){
            $fut->id_RGTirants = $value;
        }else{
            $fut->id_RSTirants = $value;
        }

        if($fut->save()){
            echo "La taille des tirants de ".$fut->size." a bien été mise à jour.";
        }else{
            echo "Erreur dans le changement de la taille des tirants.";
        }
    }

    public function editQuantity($request, $response, $args) {
        $postData = $request->getParsedBody();
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        $gamme = $args['gamme'];
        $fut = \App\Model\listFut::select()->where('id', $id)->first();
        $fut->$gamme = $value;
        $name = explode("_",$gamme);
        var_dump($name);

        if($fut->save()){
            echo "La quantité de ".$fut->size." en ".$name[1]." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement de quantité.";
        }
    }
}
