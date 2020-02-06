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
        $raw = file_get_contents('https://api.exchangeratesapi.io/latest?symbols=USD');
        $change_rate = json_decode($raw);

        $pieces = \App\Model\Piece::leftJoin('fournisseurs', 'id_fournisseur', '=', 'fournisseurs.id')
        ->leftJoin('piece_types', 'pieces.type', '=', 'piece_types.id')
        ->select('pieces.*', 'fournisseurs.name as fournisseur_name', 'fournisseurs.ref as fournisseur_ref', "piece_types.name as type", "piece_types.alert as alert")
        ->get();
        $piece_types = \App\Model\pieceType::all();
        $fournisseurs = \App\Model\Fournisseur::all();
        $tirants = \App\Model\Tirant::leftJoin('fournisseurs', 'id_fournisseur', '=', 'fournisseurs.id')
        ->leftJoin('piece_types', 'tirants.type', '=', 'piece_types.id')
        ->select('tirants.*', 'fournisseurs.name as fournisseur_name', 'fournisseurs.ref as fournisseur_ref', "piece_types.name as type", "piece_types.alert as alert")
        ->get();

        // // $list_pieces = (object)array_merge((array)$pieces,(array)$tirants);
        // $pieceLength = sizeof($pieces);
        // $this->logger->info(gettype($pieces));
        // $arrayobj = new ArrayObject($pieces);
        // foreach ($tirants as $key => $value) {
        //     # code...
        //     $this->logger->info($key);
        //     $this->logger->info($value);
        //     $id = $pieceLength+$key;
        //     // $this->logger->info($pieceLength);
        //     // $this->logger->info($id);
        //     $arrayobj->append($value);
        //     // $piece[$id]->name = "Tirant de ".$value->length;

            
        //     // array_push($pieces, $value);
        //     // foreach ($value as $k => $v) {
        //     //     # code...
        //     //     $this->logger->info($k);
        //     //     $this->logger->info($v);
        //     // }
        // }
        // foreach ($arrayobj as $k => $v) {
        //         # code...
        //         $this->logger->info($k);
        //         $this->logger->info($v);
        //     }
        $args['change_rate_USD'] = $change_rate->rates->USD;
        $args['pieces'] = $pieces;
        $args['tirants'] = $tirants;
        $args['pieceType'] = $piece_types;
        $args['fournisseurs'] = $fournisseurs;
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
        $isTirant = $args['isTirant'];
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        if($isTirant){
            $piece = \App\Model\Tirant::select()->where('id', $id)->first();
        }else{
            $piece = \App\Model\Piece::select()->where('id', $id)->first();
        }
        $piece->quantity = $value;

        if($piece->save()){
            echo "La quantité de ".$piece->name." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement de quantité.";
        }
    }

    public function editPrice($request, $response, $args) {
        $postData = $request->getParsedBody();
        $isTirant = $args['isTirant'];
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        $raw = file_get_contents('https://api.exchangeratesapi.io/latest?symbols=USD');
        $change_rate = json_decode($raw);
        $formatedVal = floatval($value);
        if(stristr($value,'$')){
            $formatedVal = $formatedVal/$change_rate->rates->USD;
        }
        if($isTirant){
            $piece = \App\Model\Tirant::select()->where('id', $id)->first();
        }else{
            $piece = \App\Model\Piece::select()->where('id', $id)->first();
        }
        
        $piece->price = $formatedVal;

        if($piece->save()){
            echo "Le prix de ".$piece->name." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement du prix.";
        }
    }

    public function editFournisseur($request, $response, $args) {
        $postData = $request->getParsedBody();
        $isTirant = $args['isTirant'];
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        if($isTirant){
            $piece = \App\Model\Tirant::select()->where('id', $id)->first();
        }else{
            $piece = \App\Model\Piece::select()->where('id', $id)->first();
        }
        $piece->id_fournisseur = $value;

        if($piece->save()){
            echo "Le fournisseur de ".$piece->name." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement de fournisseur.";
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
