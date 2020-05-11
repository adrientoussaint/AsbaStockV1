<?php



$app->get('/auth/signin', 'AuthController:getSignIn')->setName('auth.signin');
$app->post('/auth/signin', 'AuthController:postSignIn');

$app->group('', function(){
    //HOME
    $this->get('/', '\App\Controller\HomeController:home')->setName("home");
    $this->post('/addTicket', '\App\Controller\HomeController:addTicket');
    $this->post('/changeTicketStatus', '\App\Controller\HomeController:changeTicketStatus');
    
    
    // $this->get('/product', '\App\Controller\StockController:product');

    $this->get('/pdf', '\App\Controller\CommandeController:pdf')->setName('pdf');

    // FUT
    $this->get('/fut', '\App\Controller\FutController:fut')->setName('fut');
    $this->post('/fut/editQuantity/{gamme}', '\App\Controller\FutController:editQuantity');
    $this->post('/changeTirantLength/{gamme}', '\App\Controller\FutController:changeTirantLength');

    // COMMANDES
    $this->get('/order', '\App\Controller\CommandeController:order')->setName('order');
    $this->get('/order/{id}', '\App\Controller\CommandeController:orderDetail')->setName('detail');
    $this->get('/orderPdf/{id}', '\App\Controller\CommandeController:orderPdf')->setOutputBuffering(false);
    $this->post('/addOrder', '\App\Controller\CommandeController:addOrder');
    $this->post('/changeOrderStatus', '\App\Controller\CommandeController:changeOrderStatus');
    $this->post('/addPieceToOrder', '\App\Controller\CommandeController:addPieceToOrder');
    $this->post('/editQuantityOrder/{orderId}', '\App\Controller\CommandeController:editQuantityOrder');
    $this->post('/updateStock', '\App\Controller\CommandeController:updateStock');

    // PIECES
    $this->get('/piece', '\App\Controller\PieceController:piece')->setName('piece');
    $this->post('/addPiece', '\App\Controller\PieceController:addPiece');
    $this->post('/addFut', '\App\Controller\PieceController:addFut');
    $this->post('/editQuantity/{isTirant}', '\App\Controller\PieceController:editQuantity');
    $this->post('/editPrice/{isTirant}', '\App\Controller\PieceController:editPrice');
    $this->post('/editFournisseur/{isTirant}', '\App\Controller\PieceController:editFournisseur');
    $this->post('/editComment/{isTirant}', '\App\Controller\PieceController:editComment');


    // FOURNISSEURS
    $this->get('/fournisseur', '\App\Controller\FournisseurController:fournisseur')->setName('fournisseur');
    $this->post('/addFournisseur', '\App\Controller\FournisseurController:addFournisseur');
    

    // AUTH
    $this->get('/auth/signup', 'AuthController:getSignUp')->setName('auth.signup');
    $this->post('/auth/signup', 'AuthController:postSignUp');

    $this->get('/auth/signout', 'AuthController:getSignOut')->setName('signout');
    
    // DEV
    // $this->post('/importCSV', '\App\Controller\StockController:importCSV');
    // $this->get('/data', '\App\Controller\StockController:data');
    // $this->post('/createBase', '\App\Controller\StockController:createBase');
 

})->add(new App\Middleware\AuthMiddleware($container));








// 
// $app->get('/admin', '\App\Controller\AdminController:admin');

// $app->get('/logout', '\App\Controller\AdminController:logout');

// $app->post('/addProduct', '\App\Controller\StockController:addProduct');






