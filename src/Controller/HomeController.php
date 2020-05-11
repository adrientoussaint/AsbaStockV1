<?php

namespace App\Controller;

use Interop\Container\ContainerInterface;

class HomeController {
    protected $ci;
    public function __construct(ContainerInterface $ci) {
       $this->ci = $ci;
    }

    public function home($request, $response, $args) {
        $tickets = \App\Model\Ticket::all();
        $args['tickets'] = $tickets;
        $this->ci->view->render($response, "home.html", $args);
    }

    public function addTicket($request, $response, $args) {
        $new = new \App\Model\Ticket;
        $postData = $request->getParsedBody();
       
        $name = $postData['name'];
        $type = $postData['type'];   
        $description = $postData['description'];
        
        $new->name = $name;
        $new->type = $type;
        $new->description = $description;
        
        $new->save();
        return $response->withStatus(302)->withHeader('Location', '/');
    }
    public function changeTicketStatus($request, $response, $args) {
        $postData = $request->getParsedBody();
        $ticket_id = htmlspecialchars($postData['ticket_id']);
        $ticket = \App\Model\Ticket::select()->where('id', $ticket_id)->first();
        $ticket->status = !$ticket->status;
        
        if($ticket->save()){
            echo "Le statut du ticket a été mis à jour";
        }else{
            echo "Erreur dans le changement de statut.";
        }
    }
}
