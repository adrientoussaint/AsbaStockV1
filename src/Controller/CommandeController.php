<?php

namespace App\Controller;

use Slim\Http\Stream;
use Interop\Container\ContainerInterface;
use mikehaertl\wkhtmlto\Pdf;


class CommandeController {
    protected $ci;
    protected $logger;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
       $this->logger = $this->ci->get('logger');
    }
    
    public function order($request, $response, $args) {
        $orders = \App\Model\Order::join('clients', 'client_id', '=', 'clients.id')
        ->select('orders.id', 'clients.name', 'orders.date_create', 'orders.status', 'orders.ref')
        ->get();
        $fut_types = \App\Model\futType::all();
        $fut_gammes = \App\Model\futGamme::all();
        $fut_sizes = \App\Model\listFut::all();
        $fut_finitions = \App\Model\futFinition::all();
        $listClient = \App\Model\Client::all();
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
        foreach($fut_finitions as $finition){
            $list_product['finition'][$finition->id] = $finition ;
        }

        $args['orders'] = $orders;
        $args['status'] = ["En attente", "En cours", "Terminée"];
        $args['types'] = $fut_types;
        $args['gammes'] = $fut_gammes;
        $args['sizes'] = $fut_sizes;
        $args['finitions'] = $fut_finitions;
        $args['list'] = $list_product;
        $args['listClient'] = $listClient;

        $this->ci->view->render($response, "order.html", $args);
    }

    public function addOrder($request, $response, $args) {
        $postData = $request->getParsedBody();
        $fut_types = \App\Model\futType::all();
        $fut_gammes = \App\Model\futGamme::all();
        $fut_sizes = \App\Model\listFut::all();

        // Appelle de la fonction correspondant à la gamme
        $gamme = $postData['gamme'];
        $pieces = [];
        $infos = $this->order_info($postData);

        switch ($gamme) {
            case 1:
                $pieces = $this->riveGauche($postData);
                break;
            case 2:
                $pieces = $this->revelationSimone($postData,true);
                break;
            case 3:
                $pieces = $this->revelationSimone($postData,null);
                break;
            default:
                break;
        }
        

        // ::://////////////Création nouveau client///////////////////////////////////
        if($postData['listClient'] == 'null'){
            $newClient = new \App\Model\Client;
            $newClient->name = htmlspecialchars($postData['client_name']);
            $newClient->mail = htmlspecialchars($postData['client_mail']);
            $newClient->phone = htmlspecialchars($postData['client_phone']);
            $newClient->city = htmlspecialchars($postData['client_city']);
            $newClient->postal = htmlspecialchars($postData['client_postal']);
            $newClient->country = htmlspecialchars($postData['client_country']);
            $newClient->street = htmlspecialchars($postData['client_street']);
            $newClient->save();
            $client_id = $newClient->id;
        }else{
            $client_id = $postData['listClient'];
        }


        $newOrder = new \App\Model\Order;
        $newOrder->client_id = $client_id;
        $newOrder->comment = htmlspecialchars($postData['order_comment']);
        $newOrder->piece_list = serialize($pieces);
        $newOrder->order_info = serialize($infos);

        $random = rand(0, 50);
        $ref = strtoupper(substr($postData['client_name'], 0, 4)).'_'.date("d-m-y").'_'.$random;
        $newOrder->ref = $ref;
        $newOrder->save();

        // $this->logger->info('Verif des infos sélectionner par la fonction');
        // foreach ($infos as $key => $value) {
        //     $this->logger->info('key: '.$key.' value: '.$value);
        //     foreach ($value as $i => $val) {
        //         $this->logger->info('i: '.$i.' val: '.$val);
        //     }
        // }
        
        return $response->withStatus(302)->withHeader('Location', '/order');
    }

    public function order_info($datas){
        
        $i = 1;
        $infos = [];
        $gamme = \App\Model\futGamme::select()
        ->where('fut_gammes.id', $datas['gamme'] )
        ->first();
        $infos['gamme'] = $gamme->name;

        $nb_fut = $datas['nb_fut'];
        $infos['dimensions'] = [];
        $infos['types'] = [];
        while ($i <= $nb_fut) {
            // Récupère dimension fut
            // Récupère type fut
            $fut = \App\Model\listFut::select()->where([
                'list_futs.id_type' => $datas['type_fut'.$i],
                'list_futs.id' => $datas['size_fut'.$i],
            ])->first();

            $infos['dimensions'][$i] = $fut->size;
            
            $type = \App\Model\futType::select()
            ->where('fut_types.id', $datas['type_fut'.$i] )
            ->first();
            $infos['types'][$i] = $type->name;
            $i++;

        }
        return $infos;
    }

    public function revelationSimone($datas, $isSimone){
        $i = 1;
        $pieces = [];
        $nb_fut = $datas['nb_fut'];
        $piece["CA"]=1;
        
        $badge_name = !is_null($isSimone) ? "BS" : "ASB";
        
        $pieces["E"] = $pieces[$badge_name] = $nb_fut;

        while ($i <= $nb_fut) {
            // if(!is_null($isSimone)){        
            //     $ids_futs  = $isSimone ? [7,17,12] : [6,16,12];
            //     $fut = \App\Model\listFut::select()
            //     ->where('list_futs.id', $ids_futs[$i-1])
            //     ->first();
            // }else{
                $fut = \App\Model\listFut::select()->where([
                    'list_futs.id_type' => $datas['type_fut'.$i],
                    'list_futs.id' => $datas['size_fut'.$i],
                ])->first();
            // }

            $tirants = \App\Model\Tirant::select()
            ->where('tirants.id', $fut->id_RSTirants)
            ->first();
            
            $pieces["TR".$tirants->length] += $fut->nb_acas*2;

            switch ($fut->id_type) {
                case 1:
                    $key = "BD";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $cercle = !is_null($isSimone)? "CH" : "CI";
                    $pieces["CQB"] += $fut->nb_acas*2;
                    $pieces[$cercle.$diam] += 2;
                    $pieces["SPG"] += 1;
                    $pieces["SPD"] += 1;
                    $pieces["P".$key."D"] += 1;
                    $pieces["P".$key."G"] += 1;
                    $pieces["PF".$key.$diam] += 1;
                    $pieces["PR".$key.$diam] += 1;
                    $pieces["CR"] += $fut->nb_acas*2;

                    
                    $prof = $mensuration[1]; //14 - prof pour tirants

                    
                    // Pour les tirants attente reponse magicien douze et déduction de taille pour RG en fonction prof, voir comment faire pour autre gamme


                    # Grosse caisse
                     //nb coquille snare
                     //nb tirants et crochets

                    break;
                case 2:
                    # Caisse claire
                    $key = "S";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants

                    $pieces["CQS"] += $fut->nb_acas;
                    $pieces["DCL"] += 1;
                    $pieces["CP"] += 1;
                    $pieces["T".$diam] += 1;
                    $pieces["CMS".$diam] += 1;
                    $pieces["CMSD".$diam] += 1;
                    $pieces["P".$key."G"] += 1;
                    $pieces["PF".$key.$diam] += 1;
                    $pieces["PR".$key.$diam] += 1;
                    
                    break;
                case 3:
                    # Floor tom
                    $key = "FT";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants
                    $pieces["CQT"] += $fut->nb_acas*2;
                    $pieces["CM".$diam] += 2;
                    $pieces["SPG"] += 2;
                    $pieces["SPD"] += 1;
                    $pieces["P".$key] += 3;
                    $pieces["PF".$diam] += 1;
                    $pieces["PR".$diam] += 1;
                    break;
                case 4:
                    # Rack tom
                    $key = "RT";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants
                    $pieces["CQT"] += $fut->nb_acas*2;
                    $pieces["CM".$diam] += 2;
                    $pieces["PF".$diam] += 1;
                    $pieces["PR".$diam] += 1;
                    break;
                
                default:
                    # code...
                    break;
            }
            
            // $newFut->type = $postData['type_fut'.$i];
            // $newFut->gamme = $postData['gamme'];
            // $newFut->size = $postData['size_fut'.$i];
            // $newFut->finition = $postData['finition'];
            // $newFut->ref_order = $ref;
            // $newFut->save();
            $i++;
        }
        return $pieces;
    }

    public function riveGauche($datas){
        $i = 1;
        $pieces = [];
        $piece["CQS"]=0;
        $piece["CA"]=1;
        $pieces["ASB"] = $pieces["E"] = $datas['nb_fut'];
        
        while ($i <= $datas['nb_fut']) {
            $fut = \App\Model\listFut::select()->where([
                'list_futs.id_type' => $datas['type_fut'.$i],
                'list_futs.id' => $datas['size_fut'.$i],
            ])->first();

            $tirants = \App\Model\Tirant::select()
            ->where('tirants.id', $fut->id_RGTirants)
            ->first();
            
            $pieces["TR".$tirants->length] += $fut->nb_acas*2;

            switch ($datas['type_fut'.$i]) {
                case 1:
                    $key = "BD";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants
                    $pieces["CQS"] += $fut->nb_acas;
                    $pieces["CI".$diam] += 2;
                    $pieces["SPG"] += 1;
                    $pieces["SPD"] += 1;
                    $pieces["P".$key."D"] += 1;
                    $pieces["P".$key."G"] += 1;
                    $pieces["PF".$key.$diam] += 1;
                    $pieces["PR".$key.$diam] += 1;
                    $pieces["CR"] += $fut->nb_acas*2;
                    
                    // Pour les tirants attente reponse magicien douze et déduction de taille pour RG en fonction prof, voir comment faire pour autre gamme


                    # Grosse caisse
                     //nb coquille snare
                    $fut->nb_acas*2; //nb tirants et crochets

                    break;
                case 2:
                    # Caisse claire
                    $key = "S";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants

                    $pieces["CQS"] += $fut->nb_acas;
                    $pieces["DCL"] += 1;
                    $pieces["CP"] += 1;
                    $pieces["T".$diam] += 1;
                    $pieces["CES".$diam] += 1;
                    $pieces["CESD".$diam] += 1;
                    $pieces["P".$key."G"] += 1;
                    $pieces["PF".$key.$diam] += 1;
                    $pieces["PR".$key.$diam] += 1;
                    
                    break;
                case 3:
                    # Floor tom
                    $key = "FT";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants
                    $pieces["CQS"] += $fut->nb_acas;
                    $pieces["CE".$diam] += 2;
                    $pieces["SPG"] += 2;
                    $pieces["SPD"] += 1;
                    $pieces["P".$key] += 3;
                    $pieces["PF".$diam] += 1;
                    $pieces["PR".$diam] += 1;
                    break;
                case 4:
                    # Rack tom
                    $key = "RT";
                    $mensuration = explode("x",$fut->size); //18x14
                    $diam = $mensuration[0]; //18 - diam pour cercle et peau
                    $prof = $mensuration[1]; //14 - prof pour tirants
                    $pieces["CQS"] += $fut->nb_acas;
                    $pieces["CE".$diam] += 2;
                    $pieces["PF".$diam] += 1;
                    $pieces["PR".$diam] += 1;
                    break;
                
                default:
                    # code...
                    break;
            }
            
            $i++;
        }
        return $pieces;
    }

    
    public function changeOrderStatus($request, $response, $args) {
        $postData = $request->getParsedBody();
        $id = htmlspecialchars($postData['pk']);
        $value = htmlspecialchars($postData['value']);
        $order = \App\Model\Order::select()->where('id', $id)->first();
        $order->status = $value;

        if($order->save()){
            echo "Le statut de la commande pour ".$order->client_name." a bien été mise à jour. ";
        }else{
            echo "Erreur dans le changement de statut.";
        }
    }

    public function orderDetail($request, $response, $args) {
        $order = \App\Model\Order::select()->where('id', $args['id'])->first();
        $client = \App\Model\Client::select()->where('id', $order->client_id)->first();
        $array_ref=[];
        $array_qty=[];
        if($order){
            $orderDetails = $order->join('clients', 'client_id', '=', 'clients.id')->get();
            $list_pieces = unserialize($order->piece_list);

            foreach ($list_pieces as $ref => $qty) {
                $this->logger->info($ref.'=>'.$qty);
                if(stristr($ref, 'TR')){
                    $newRef = explode('R', $ref);
                    $ref = $newRef[1];
                }
                array_push($array_ref, $ref);
                array_push($array_qty, $qty);
            }
            $pieces = \App\Model\Piece::select()->whereIn('ref', $array_ref)->get();
            $tirants = \App\Model\Tirant::select()->whereIn('length', $array_ref)->get();
            foreach ($pieces as $key => $piece) {
                $key = array_search($piece->ref, $array_ref);
                if(!is_null($key)){
                    $piece->quantity = $array_qty[$key];
                }
            }
            foreach ($tirants as $key => $tirant) {
                $key = array_search($tirant->length, $array_ref);
                if(!is_null($key)){
                    $tirant->quantity = $array_qty[$key];
                }
            }
        }
        
        // foreach ($pieces as $key => $value) {
        //         if(stristr($value->name, "cheur")){
        //             $this->logger->info('key: '.$key.' value: '.$value);
        //         }
        //     }

        $args['listAllPieces'] = \App\Model\Piece::all();
        $args['client'] = $client;
        $args['pieces'] = $pieces;
        $args['tirants'] = $tirants;
        $args['order'] = $order;
        $this->ci->view->render($response, "orderDetail.html", $args);
    }

    public function addPieceToOrder($request, $response, $args){
        $orderId = $request->getParam('orderId');
        $quantity = intval($request->getParam('qty'));
        $reference = $request->getParam('ref');
        $order = \App\Model\Order::select()->where('id', $orderId)->first();
        $list_pieces = $this->changeQuantityOrderPiece($order, $reference, $quantity, true);


        

        $order->piece_list = serialize($list_pieces);
        if($order->save()){
            $response = ["message"=>"Votre pièce à bien été ajoutée à la commande ".$order->ref, "status" => false];
        }else{
            $response = ["message"=>"Erreur d'enregistrement en base", "status" => true];
        }
        return json_encode($response);
        
    }

    public function editQuantityOrder($request, $response, $args) {
        $orderId = $args['orderId'];
        $postData = $request->getParsedBody();
        $reference = htmlspecialchars($postData['pk']);//ref
        $quantity = htmlspecialchars($postData['value']);//new val
        $piece = \App\Model\Piece::select()->where('ref', $reference)->first();
        $order = \App\Model\Order::select()->where('id', $orderId)->first();
        $list_pieces = $this->changeQuantityOrderPiece($order, $reference, $quantity, false);
        $order->piece_list = serialize($list_pieces);

        if($order->save()){
            echo "La quantité de ".$piece->name." a bien été mise à jour.";
        }else{
            echo "Erreur dans le changement de quantité.";
        }
        return $response;
    }

    public function changeQuantityOrderPiece($order, $reference, $quantity, $add){
        $list_pieces = unserialize($order->piece_list);
        $array_ref=[];
        if($add){
            foreach ($list_pieces as $ref => $qty) {
                array_push($array_ref, $ref);
                
                if($ref === $reference){
                    $list_pieces[$ref] += $quantity;
                    break;
                }
            }
    
            if(!in_array($reference, $array_ref)){
                $list_pieces[$reference] = $quantity;
            }
        }else{
            foreach ($list_pieces as $ref => $qty) {
                array_push($array_ref, $ref);
                
                if($ref === $reference){
                    $list_pieces[$ref] = $quantity;
                    break;
                }
            }
        }

        return $list_pieces;
    }

    public function updateStock($request, $response, $args) {
        $postData = $request->getParsedBody();
        $ref_order = $postData['order_ref'];
        $id_order = $postData['order_id'];

        $order = \App\Model\Order::select()->where([
            'id' => $id_order,
            'ref' => $ref_order
        ])->first();

        $list_pieces = unserialize($order->piece_list);

        $array_ref=[];
        $array_qty=[];

        foreach ($list_pieces as $ref => $qty) {
            array_push($array_ref, $ref);
            array_push($array_qty, $qty);
        }

        $pieces = \App\Model\Piece::select()->whereIn('ref', $array_ref)->get();
        
        foreach ($pieces as $key => $piece) {
            // $this->logger->info('$piece->quantity'.$piece->quantity);
            foreach ($list_pieces as $ref => $qty) {
                if($piece->ref==$ref){
                    $piece->quantity -= $qty;
                    // $this->logger->info('$piece->quantity'.$piece->quantity);
                    $piece->save();
                    break;
                }
            }
        }
            
    }

    public function orderPdf($request, $response, $args){
        $type_pdf = $request->getQueryParams()['type_pdf'];
        
        $orderId = $args['id'];
        $order = \App\Model\Order::leftJoin('clients', 'client_id', '=', 'clients.id')
        ->where('orders.id', $orderId)->first();
        $array_ref=[];
        $array_qty=[];
        if($order){
            $list_pieces = unserialize($order->piece_list);
            foreach ($list_pieces as $ref => $qty) {
                array_push($array_ref, $ref);
                array_push($array_qty, $qty);
            }
            $pieces = \App\Model\Piece::select()->whereIn('ref', $array_ref)->get();
            foreach ($pieces as $key => $piece) {
                $key = array_search($piece->ref, $array_ref);
                if(!is_null($key)){
                    $piece->quantity = $array_qty[$key];
                }
            }

            $infos = unserialize($order->order_info);
            // $this->logger->info('Verif des infos sélectionner par la fonction');
            // foreach ($infos as $key => $value) {
            //     $this->logger->info('key: '.$key.' value: '.$value);
            //     if($key == "dimensions"){
            //         foreach ($value as $i => $dimension) {
            //             $this->logger->info('key: '.$i.' dimension: '.$dimension);
            //         }
            //     }
            //     if($key == "types"){
            //         foreach ($value as $i => $type) {
            //             $this->logger->info('key: '.$i.' type: '.$type);
            //         }
            //     }
            // }
            
            
            $args['gamme'] = $infos['gamme'];
            $args['types'] = $infos['types'];
            $args['dimensions'] = $infos['dimensions'];
            $args['pieces'] = $pieces;
            $args['order'] = $order;
    
            // $this->logger->info('$order');
            // $this->logger->info($order);
            // $this->logger->info('$pieces');
            // $this->logger->info($pieces);
            
            switch ($type_pdf) {
                case 'fdc':
                    ob_start();
                    $content = $this->ci->view->fetch('pdf\ficheDeProduction.html', $args);
                    ob_get_clean();
                    break;
                case 'lp':
                    ob_start();
                    $content = $this->ci->view->fetch('pdf\listPieces.html', $args);
                    ob_get_clean();
                    break;
                
                default:
                    # code...
                    break;
            }
            $pdf = new Pdf;
            $footer = '<!DOCTYPE html>
            <html>  
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" >
            </head>  
            <body>  
                <div class="container-fluid" id="footer">
                    <div class="row">
                        <div class="col-12 text-center">
                            <p class="m-0"><strong>ASBA</strong> 31 rue de Cuire Bât. G 69004 Lyon - 04.78.39.80.30 - 1927@asbadrums.com</p>
                        </div>
                    </div>
                </div>
            </body>  
            </html> ';
            $globalOptions = array(
                // 'margin-right' => 10,
                // 'margin-left' => 10,
                // 'margin-top' => 10,
                // 'footer-html' => __DIR__.'/../templates/pdf/footer.html',
            );
            $pdf->setOptions($globalOptions);
            // $pdf->binary = 'lib/wkhtmltox/bin/wkhtmltopdf';
            $pageOptions = array(
                // 'javascript-delay' => 2000,
                'encoding'         => 'UTF-8',
                // 'footer-line',
                'footer-html' => $footer,
                // 'footer-left'      => strtoupper($field_product_line),
                // 'footer-right'     => "[page]",
                // 'footer-font-size' => 10,
                // 'footer-spacing'   => 10
            );
            $pdf->addPage($content, $pageOptions);
            $pdf->send();
    
            // $this->ci->view->render($response,'pdf\ficheDeProduction.html', $args);
        }
        
    }
}
