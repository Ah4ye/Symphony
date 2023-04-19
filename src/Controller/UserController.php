<?php

namespace App\Controller;

use App\Entity\Panier;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use App\Form\ProfilType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
    }

    #[Route('/profil', name: 'app_profil')]
    public function edit( Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher) : Response
    {
        $user = $this->getUser();
        $mdp = $user->getPassword();
        $form = $this->createForm(ProfilType::class, $user);
        $form->add('send', SubmitType::class, ['label' => 'Modifier']);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('password')->getData();
            if ($password !== $mdp) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            }
            $em->flush();
            return $this->redirectToRoute('accueil_index');
        }
        $args = array(
            'myform' => $form->createView(),
        );
        return $this->render('User/interface.html.twig',$args);
    }
    #[Route('/gere_client', name: 'app_admin')]
    public function ActionSuperAdmin( Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher) : Response
    {
        $UserRepository = $em->getRepository(User::class);
        $Users = $UserRepository->findAll();
        $Groupe_CLients = [];
        if ( !empty($Users)) {
            foreach ($Users as $User) {
                $idUser = $User->getId();
                $login = $User->getLogin();
                $nom = $User->getName();
                $role = $User->getRoles();
                $password = $User->getPassword();

                $Groupe_CLient = [
                    'id' => $idUser,
                    'nom' => $nom,
                    'login' => $login,
                    'role' => $role,
                    'pssdw' => $password,
                ];
                array_push($Groupe_CLients,$Groupe_CLient);
            }
        }
        $args = array(
            'utilisateurs' => $Groupe_CLients,
        );
        return $this->render('User/super_adm.html.twig',$args);
    }


    #[Route(
        '/delete_user/{id}',
        name: 'delete_user',
        requirements: ['id' => '[1-9]\d*'],
    )]
    public function deleteUserAction(int $id, EntityManagerInterface $em, AuthorizationCheckerInterface $authChecker): Response
    {
        $UserRepository = $em->getRepository(User::class);
        $User = $UserRepository->findOneBy(['id' => $id]);
        $UserCourant = $this->getUser()->getId();

        if ((!in_array('ROLE_ADMIN', $User->getRoles())) &&  ($UserCourant !== $id)){
        $panierRepository = $em->getRepository(Panier::class);
        $panier = $panierRepository->findOneBy(['Client' => $id]);
        $panierProduits = $panier->getPaniersProduits();

            if (!empty($panier)) {
                foreach ($panierProduits as $panierProduit) {
                    $em->remove($panierProduit);
                }
                $em->remove($panier);
            }
            $em->remove($User);
            $em->flush();
            return $this->redirectToRoute('app_admin');
        }
        $this->addFlash('info', 'On ne triche pas ici :) ');
        return $this->redirectToRoute('app_admin');
    }

}
