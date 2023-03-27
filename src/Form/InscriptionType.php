<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;


use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;


class InscriptionType extends AbstractType
{

    private UserPasswordHasherInterface $passwordHasher ;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }


    public function buildForm(FormBuilderInterface $builder, array $options ): void
    {

        $builder
            ->add('login', TextType::class, [
                     'label' => 'Login',
             ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Role',
                'choices' => [
                    'Client' => 'ROLE_CLIENT',
                    'Gestionaire' => 'ROLE_GESTION',
                    'Dirigeant' => 'ROLE_DIRIGEANT',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'multiple' => true,
            ])
             ->add('password', RepeatedType::class, [
                     'type' => PasswordType::class,
                     'invalid_message' => 'The password fields must match.',
                     'options' => ['attr' => ['class' => 'password-field']],
                     'required' => true,
                     'first_options'  => ['label' => 'Password'],
                     'second_options' => ['label' => 'Repeat Password'],
                 ])
             ->add('name', TextType::class, [
                     'label' => 'Name',
                 ]);
                    // Oui je sais c'est pas beau mais je hash ici, juste apres que le formulaire soit fourni
        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $user = $event->getData();


            $password = $user->getPassword();
            if ($password) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
                $user->setPassword($hashedPassword);
            }
        });
    }




    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
