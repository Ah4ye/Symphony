<?php

namespace App\Controller;

use App\Form\InscriptionType;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

#[Route(path: '/security', name: 'security')]
class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: '_login')]
    public function loginAction(AuthenticationUtils $authenticationUtils): Response
    {

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('Security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: '_logout')]
    public function logoutAction(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/inscription', name: '_inscription')]
    public function inscriptionAction(EntityManagerInterface $em, Request $request,UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('info', 'view : Inscription deja faite ');
                return $this->redirectToRoute('accueil_index');
        }

            $user = new User();

        $form = $this->createForm(InscriptionType::class, $user);
        $form->add('send', SubmitType::class, ['label' => 'Sinscrire']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            // Je hash avant d'envoyer dans la base de donnée
            $user->setPassword($hashedPassword);
            $em->persist($user);
            $em->flush();
            $this->addFlash('info', 'Bienvenue parmis nous, tu es un nouveau né');
            return $this->redirectToRoute('accueil_index');    // il faudrait l'action qui liste les magasins
        }

        if ($form->isSubmitted())

            $this->addFlash('info', 'formulaire inscription incorrect');

        $args = array(
            'myform' => $form->createView(),
        );
        return $this->render('Security/add.html.twig', $args);

    }
}
