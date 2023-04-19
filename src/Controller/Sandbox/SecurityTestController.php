<?php

namespace App\Controller\Sandbox;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
#use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/sandbox/securitytest', name: 'sandbox_securitytest')]
class SecurityTestController extends AbstractController
{
    #[Route('/addusers', name: '_addusers')]
    public function addUsersAction(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $user
            ->setLogin('jarod')
            ->setName('Le Caméléon')
            ->setRoles(['ROLE_CLIENT']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'toto');
        $user->setPassword($hashedPassword);
        $em->persist($user);

        $user = new User();
        $user
            ->setLogin('parker')
            ->setName('Mlle Parker')
            ->setRoles(['ROLE_SALARIE']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'azerty');
        $user->setPassword($hashedPassword);
        $em->persist($user);

        $user = new User();
        $user
            ->setLogin('sidney')
            ->setName('Sidney')
            ->setRoles(['ROLE_SALARIE', 'ROLE_GESTION']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'password');
        $user->setPassword($hashedPassword);
        $em->persist($user);

        $user = new User();
        $user
            ->setLogin('raines')
            ->setName('William Raines')
            ->setRoles(['ROLE_DIRIGEANT']);
        $hashedPassword = $passwordHasher->hashPassword($user, '123456');
        $user->setPassword($hashedPassword);
        $em->persist($user);

        $user = new User();
        $user
            ->setLogin('angelo')
            ->setName('Angelo')
            ->setRoles(['ROLE_GESTION']);
        $hashedPassword = $passwordHasher->hashPassword($user, 'qwerty');
        $user->setPassword($hashedPassword);
        $em->persist($user);

        $em->flush();

        return new Response('<body>ICI</body>');
    }

    #[Route('/accessuser', name: '_accessuser')]
    public function accessUserAction(Security $security): Response
    {
        //dump($this->getUser());
        dump($security->getUser());
        dump($security->getUser()->getRoles());
        return $this->render('Sandbox/SecurityTest/access_user.html.twig');
    }

    #[Route('/role1', name: '_role1')]
    #[IsGranted('ROLE_SALARIE')]
    public function role1Action(): Response
    {
        return new Response('<body>IsGranted(\'ROLE_SALARIE\')</body>');
    }

    #[Route('/role2', name: '_role2')]
    #[IsGranted('ROLE_SALARIE', statusCode: 404, message: 'No access! Get out!')]
    #[IsGranted('ROLE_GESTION')]
    public function role2Action(): Response
    {
        return new Response('<body>IsGranted(\'ROLE_SALARIE\') et IsGranted(\'ROLE_GESTION\')</body>');
    }

    #[Route('/role3', name: '_role3')]
    #[\Sensio\Bundle\FrameworkExtraBundle\Configuration\Security(
        "is_granted('ROLE_SALARIE') or is_granted('ROLE_GESTION')"
    )]
    public function role3Action(): Response
    {
        return new Response('<body>IsGranted(\'ROLE_SALARIE\') or IsGranted(\'ROLE_GESTION\')</body>');
    }

    #[Route('/role4', name: '_role4')]
    // pas d'annotation : mais le fichier security.yaml indique qu'on doit être authentifié
    public function role4Action(Security $security): Response
    {
        //if (! $this->isGranted('ROLE_SALARIE'))
        if (! $security->isGranted('ROLE_SALARIE'))
            throw new AccessDeniedException('Vous n\'êtes pas salarié');

        $this->denyAccessUnlessGranted('ROLE_GESTION', null, 'Vous n\'êtes pas gestionnaire');

        return new Response('<body>IsGranted dans l\'action</body>');
    }

    #[Route('/role5', name: '_role5')]
    #[IsGranted('ROLE_SALARIE')]
    public function role5Action(): Response
    {
        if ($this->getUser()->getUserIdentifier() === 'sidney')
            throw new AccessDeniedException('Vous n\'avez pas l\'accréditation nécessaire');
        return new Response('<body>Tests dans les annotations et le code</body>');
    }

    #[Route('/role6', name: '_role6')]
    #[IsGranted('ROLE_SALARIE')]
    public function role6Action(): Response
    {
        $msg = 'ok pour lister les films';
        if ($this->isGranted('ROLE_DIRIGEANT'))
            $msg .= ' (avec droit de suppression)';
        return new Response('<body>' . $msg . '</body>');
    }

    #[Route('/role7', name: '_role7')]
    #[IsGranted('ROLE_SALARIE')]
    public function role7Action(): Response
    {
        return $this->render('Sandbox/SecurityTest/role7.html.twig');
    }
}
