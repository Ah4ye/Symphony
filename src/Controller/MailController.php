<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\SendMailService;


class MailController extends AbstractController
{
    #[Route('/mail', name: 'app_mail')]
    public function PlainteAction(Request $request, SendMailService $mail) : Response
    {
        $User = $this->getUser();
        $MailUser = $request->request->get('Mail');
        $NomProduit = $request->request->get('produit');
        $NbrProduit = $request->request->get('nbr_produits');
        $Message = $request->request->get('Message_Mail');
        $context = [
            'mail' => $MailUser,
            'message' => $Message,
            'sujet' => 'Produit : '.$NomProduit,
            'nom' => $User->getName(),
            'produits' => $NbrProduit,
        ];
        if (!empty($MailUser) && is_string($MailUser)) {
            $mail->send(
                $MailUser,
                'arthur1.haye@gmail.com',
                'Plainte de Produit',
                'Send_Mail',
                $context,
            );
            $this->addFlash('info', 'Plainte Envoyer');
        }
        else {  $this->addFlash('info', 'Plainte NON Envoyer'); }

        return $this->render('Accueil/index.html.twig');

    }
}
