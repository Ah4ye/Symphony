<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Validator\Constraints\NotBlank;


class InscriptionType extends AbstractType
{

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    public function buildForm(FormBuilderInterface $builder, array $options ): void
    {


        $user = $this->security->getUser();

        // On définit les choix de rôles en fonction du rang de l'utilisateur
        $roleChoices = [
            'Client' => 'ROLE_CLIENT',
        ];
        if ($user && in_array('ROLE_GESTION', $user->getRoles())) {
            $roleChoices['Gestionnaire'] = 'ROLE_GESTION';
        }
        if ($user && in_array('ROLE_DIRIGEANT', $user->getRoles())) {
            $roleChoices['Dirigeant'] = 'ROLE_DIRIGEANT';
        }
        if ($user && in_array('ROLE_ADMIN', $user->getRoles())) {
            $roleChoices['Administrateur'] = 'ROLE_ADMIN';
        }

        $builder
            ->add('login', TextType::class, [
                     'label' => 'Login',
             ])
            ->add('roles', ChoiceType::class, [
                'label' => 'Role',
                'choices' => $roleChoices,
                'expanded' => true,
                'multiple' => true,
            ])
             ->add('password', RepeatedType::class, [
                     'type' => PasswordType::class,
                     'invalid_message' => 'The password fields must match.',
                     'options' => ['attr' => ['class' => 'password-field']],
                     'required' => true,
                 'first_options'  => ['label' => 'Password', 'constraints' => [new NotBlank()]],
                 'second_options' => ['label' => 'Repeat Password', 'constraints' => [new NotBlank()]],
                 ])
             ->add('name', TextType::class, [
                     'label' => 'Name',
                 ])
            ->add('dateOfBirth', BirthdayType::class, [
                'label' => 'Date of Birth',
            ]);


    }




    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
