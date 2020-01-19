<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class FournisseurController {
    protected $ci;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
       $this->logger = $this->ci->get('logger');
    }

    public function fournisseur($request, $response, $args) {
        $fournisseurs = \App\Model\Fournisseur::all();
        $pieces = \App\Model\Piece::select()->where('id_fournisseur', null)->get();
        
        $args['fournisseurs'] = $fournisseurs;
        $args['pieces'] = $pieces;
        
        $this->ci->view->render($response, "fournisseur.html", $args);
    }
    
    public function addFournisseur($request, $response, $args) {
        $new = new \App\Model\Fournisseur;
        $postData = $request->getParsedBody();
        $name = $postData['name'];
        $ref = $postData['ref'];
        $mail = $postData['mail'];
        $phone = $postData['phone'];
        $commentaire = $postData['commentaire'];
        $piecesFourni = $postData['pieceFourni'];

        $new->name = $name;
        $new->ref = $ref;
        $new->mail = $mail;
        $new->phone = $phone;
        $new->commentaire = $commentaire;
        $new->save();
        

        foreach ($piecesFourni as $id) {
            $piece = \App\Model\Piece::select()->where('id', $id)->first();
            $piece->id_fournisseur = $new->id;
            $piece->save();
        }
        return $response->withRedirect($this->ci->router->pathFor('fournisseur'));
    
    }
}
