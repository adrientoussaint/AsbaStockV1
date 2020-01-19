<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class PieceController {
    protected $ci;
    protected $logger;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
       $this->logger = $this->ci->get('logger');
    }

    public function piece($request, $response, $args) {
        $pieces = \App\Model\Piece::leftJoin('fournisseurs', 'id_fournisseur', '=', 'fournisseurs.id')
        ->leftJoin('piece_types', 'pieces.type', '=', 'piece_types.id')
        ->select('pieces.*', 'fournisseurs.name as fournisseur_name', 'fournisseurs.ref as fournisseur_ref', "piece_types.name as type")
        ->get();
        $piece_types = \App\Model\pieceType::all();

        $args['pieces'] = $pieces;
        $args['pieceType'] = $piece_types;
        $this->ci->view->render($response, "piece.html", $args);
    }

    public function addPiece($request, $response, $args) {
        $new = new \App\Model\Piece;
        $postData = $request->getParsedBody();
        $name = $postData['name'];
        $ref = $postData['ref'];
        $type = $postData['type'];
        $quantity = $postData['quantity'];
        $price = $postData['price'];
        $size = !empty($postData['size']) ? $postData['size'] : null;    
        $description = $postData['description'];
        $new->name = $name;
        $new->ref = $ref;
        $new->type = $type;
        $new->quantity = $quantity;
        $new->price = $price;
        $new->size = $size;
        $new->description = $description;
        
        $new->save();
        return $response->withStatus(302)->withHeader('Location', '/piece');
    }

    public function editQuantity($request, $response, $args) {
        $postData = $request->getParsedBody();
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        $piece = \App\Model\Piece::select()->where('id', $id)->first();
        $piece->quantity = $value;

        if($piece->save()){
            echo "La quantité de ".$piece->name." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement de quantité.";
        }
    }

    public function editPrice($request, $response, $args) {
        $postData = $request->getParsedBody();
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        $piece = \App\Model\Piece::select()->where('id', $id)->first();
        $formatedVal = floatval($value);
        $piece->price = $formatedVal;

        if($piece->save()){
            echo "Le prix de ".$piece->name." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement du prix.";
        }
    }

    public function addFut($request, $response, $args) {
        $new = new \App\Model\Fut;
        $postData = $request->getParsedBody();
        $type = $postData['type'];
        $gamme = $postData['gamme'];
        $size = $postData['size'];
        $new->type = $type;
        $new->size = $size;
        $new->gamme = $gamme;
        
        $new->save();
        return $response->withStatus(302)->withHeader('Location', '/product');
    }
}
