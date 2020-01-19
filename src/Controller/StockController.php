<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class StockController {
    protected $ci;
    protected $logger;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
       $this->logger = $this->ci->get('logger');
    }

    public function product($request, $response, $args) {
        $futs = \App\Model\Fut::all();
        $fut_types = \App\Model\futType::all();
        $fut_gammes = \App\Model\futGamme::all();
        $fut_sizes = \App\Model\listFut::all();
        $list_product = [];
        foreach($fut_sizes as $size){
            $list_product['size'][$size->id] = $size ;
        }
        foreach($fut_gammes as $gamme){
            $list_product['gamme'][$gamme->id] = $gamme ;
        }
        foreach($fut_types as $type){
            $list_product['type'][$type->id] = $type ;
        }

        $args['produits'] = $futs;
        $args['types'] = $fut_types;
        $args['gammes'] = $fut_gammes;
        $args['sizes'] = $fut_sizes;
        $args['list'] = $list_product;
        $this->ci->view->render($response, "product.html", $args);
    }

    public function data($request, $response, $args) {
        $this->ci->view->render($response, "csv.html", $args);
    }

    public function createBase($request, $response, $args) {
        $commun = ["CA" => "Clé Accordage", "BS" => "Badge Simone", "ASB" => "Asbadge", "E" => "Event", "CR" => "Crochet", "DCL" => "Déclencheur", "CPT" => "Contrepartie", "CQS" => "Coquille Snare", "CQT" => "Coquille Tom", "CQB" => "Coquille GC", 
        "SPD" => "Support Pied Droit", "SPG" => "Support Pied Gauche", "SPT" => "Support Tom", "PBDD" => "Pied GC Droit", "PBDG" => "Pied GC Gauche", "PFT" => "Pied Floor Tom" ];
        
        switch ($_POST["type"]) {
            case 0:
                // SNARE
                $keyword = "S";
                $diam = ["T" => "Timbre", "PF".$keyword => "Peau de frappe Snare", "PR".$keyword => "Peau de resonnance Snare", "CMS" => "Cercle moulé Snare", "CES" => "Cercle embouti Snare", "CMSD" => "Cercle moulé Snare dessous","CESD" => "Cercle embouti Snare dessous" ];
                $currDiam = [14];
                break;
            case 1:
                // Bass Drum
                $keyword = "BD";
                $diam = ["PF".$keyword=>"Peau de frappe GC", "PR".$keyword=>"Peau de resonnance GC", "CI" => "Cercle Inlay", "CH" => "Cercle Hêtre"];
                $currDiam = [18,20,22,24];
                break;
            case 2:
                // Floor & Rack Tom
                $diam = ["PF"=>"Peau de frappe", "PR"=>"Peau de resonnance", "CE" => "Cercle Embouti", "CM" => "Cercle Moulé"];
                $currDiam = [10,12,13,14,16,18];
                break;
            
            default:
                
                break;
        }
        
        $pieceType = \App\Model\pieceType::all();
        foreach ($commun as $key => $value) {

            $pieceExist = \App\Model\Piece::select()->where('name', $value)->get();

            if(count($pieceExist) == 0){
                $newPiece = new \App\Model\Piece;
            
                foreach ($pieceType as $type) {
    
                    if(stristr($value,$type->name) != false){
                        $newPiece->type = $type->id;
                        break;
                    }else{
                        $newPiece->type = 14;
                    }
                }
    
                $newPiece->name = $value;
                $newPiece->ref = $key;
                $newPiece->quantity = 100;
                $newPiece->price = 12;
                $newPiece->ref_fournisseur = "un fournisseur";
                $newPiece->save();
            }
        }
        
        foreach ($diam as $key => $value) {
            foreach ($currDiam as $currD) {
                $currName = $value.' '.$currD;
                $pieceExist = \App\Model\Piece::select()->where('name', $currName)->get();

                if(count($pieceExist) == 0){
                    $newPiece = new \App\Model\Piece;
                
                    foreach ($pieceType as $type) {
        
                        if(stristr($value,$type->name) != false){
                            $newPiece->type = $type->id;
                            break;
                        }else{
                            $newPiece->type = 14;
                        }
                        // $this->logger->info($test);
                    }
                    
                    $newPiece->name =  $currName;
                    $newPiece->ref = $key.$currD;
                    $newPiece->quantity = 100;
                    $newPiece->price = 12;
                    $newPiece->ref_fournisseur = "un fournisseur";
                    $newPiece->save();        
                }
            }
        }
        return "Base crée";
    }

    // public function importCSV($request, $response, $args) {
    //     if (isset($_POST["import"])) {
    
    //         $fileName = $_FILES["file"]["tmp_name"];
            
    //         if ($_FILES["file"]["size"] > 0) {
                
    //             $file = fopen($fileName, "r");
    //             $type = 1;
    //             $types = ["Grosses caisses","Snare", "Floor Tom ", "Rack Tom "];

    //             while (($column = fgetcsv($file, 10000, ";")) !== FALSE) {
    //                 if (in_array($column[0], $types)) {
    //                     switch($column[0]){
    //                         case "Grosses caisses":
    //                             $type = 1;
    //                             break;
    //                         case "Snare":
    //                             $type = 2;
    //                             break;
    //                         case "Floor Tom ":
    //                             $type = 3;
    //                             break;
    //                         case "Rack Tom ":
    //                             $type = 4;
    //                             break;
    //                     }
    //                 }else{
    //                     $replacedName = str_replace(['(',')'],'',$column[0]);
    //                     $pieceExist = \App\Model\Piece::select()->where('name', $replacedName)->get();
    //                     $this->logger->info($pieceExist);
    //                     $fut_size = \App\Model\listFut::select()->where('id_type', $type)->get();
    //                     if(count($pieceExist) == 0){
    //                         // Toutes les possibilités de taille en fonction du type de fut
    //                         $this->logger->info($column[0].'/'.$column[1].'/'.$column[2].'/'.$column[3].'/'.$column[4].'/'.$column[5].'/'.$column[6].'/'.$column[7].'/'.$column[8].'/'.$column[9].'/'.$column[10].'/'.$column[11].'/'.$column[12].'/'.$column[13].'/'.$column[14].'/'.$column[15].'/'.$column[16].'/');
    //                         $newPiece = new \App\Model\Piece;
    //                         $newPiece->name = $replacedName;
    //                         $newPiece->id_gamme = 3;

    //                         $array_ref_type = [];
    //                         array_push($array_ref_type, $type);
    //                         $newPiece->ids_type = serialize($array_ref_type);

    //                         $newPiece->quantity = 100;
    //                         $newPiece->price = str_replace(',','.',$column[10]);
    //                         $array_ref_size = [];
    //                         foreach ($fut_size as $key => $size) {
    //                             if (!empty($column[$key+1])) {      
                                    
    //                                 array_push($array_ref_size, $size->id);                                 
    //                             }
                                
    //                         }
    //                         $newPiece->ids_size = serialize($array_ref_size);
    //                         $newPiece->type = 1; //Trouver soluce pour ça
    //                         $newPiece->ref_fournisseur = $column[19];
    //                         $newPiece->save();
    //                         // var_dump($array_ref_size);
                            
    //                     }else{
    //                         // $array_ref_type = unserialize($pieceExist[0]->ids_type);
    //                         // if(!in_array($type, $array_ref_type)){
    //                         //     array_push($array_ref_type, $type);
    //                         //     $pieceExist[0]->ids_type = serialize($array_ref_type);
    //                         //     $pieceExist[0]->save();
    //                         // }

    //                         $array_ref_size = unserialize($pieceExist[0]->ids_size);
    //                         $this->logger->info("MâchePô ".$column[0]);
    //                         foreach ($fut_size as $key => $size) {
    //                             if(!in_array($size->id, $array_ref_size)){
    //                                 if (!empty($column[$key+1])) {
    //                                     array_push($array_ref_size, $size->id); 
    //                                     $pieceExist[0]->ids_size = serialize($array_ref_size);
    //                                     $pieceExist[0]->save();                       
    //                                 }
    //                             }
                                
    //                         }
    //                     }

    //                 }
    //                 //
    //                 //if($column[0] == "Snare"){
    //                     // Code pour ajouter Size
    //                     // while ($i <= 6) {
    //                     //     # code...
    //                     //     if (!empty($column[$i])) {
    //                     //         $newSize = new \App\Model\listFut;
    //                     //         $newSize->size = $column[$i];
    //                     //         $newSize->id_type = 3;
    //                     //         $newSize->save();
    //                     //     }
    //                     //     $i++;
    //                     // }
    //                 //}
                    
    //             }
    //         }
    //     }
    // }

    public function addProduct($request, $response, $args) {
        
        // $new = new \App\Model\Product;
        // $postData = $request->getParsedBody();
        // $productName = htmlspecialchars($postData['name']);
        // $productRef = htmlspecialchars($postData['ref']);
        // $productPrice = htmlspecialchars($postData['price']);
        // $productQuantity = htmlspecialchars($postData['quantity']);
        // $productdescription = htmlspecialchars($postData['description']);
        // $producttype = htmlspecialchars($postData['type']);
        
        // $new->name = $productName;
        // $new->ref = $productRef;
        // $new->price = $productPrice;
        // $new->quantity = $productQuantity;
        // $new->description = $productdescription;
        // $new->type = $producttype;
        // $new->save();
        return $response->withStatus(302)->withHeader('Location', '/product');
    }
}
