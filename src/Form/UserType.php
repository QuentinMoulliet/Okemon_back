<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // ->add('nickname')
            // ->add('email')
            // ->add('password')
            ->add('roles',ChoiceType::class,[
                "multiple" => true,
                "expanded" => true,
                "choices" => [
                    "Utilisateur" => "ROLE_USER",
                    "Admin" => "ROLE_ADMIN"
                ],
                'label' => 'Veuillez sélectionnez son rôle :'
                ])
            // ->add('age')
            // ->add('country')
            // ->add('description')
            // ->add('catchphrase')
            // ->add('image')
            ->add('status',ChoiceType::class,[
                "expanded" => true,
                "choices" => [
                    "Actif" => 1,
                    "Désactivé (ou bloqué)" => 2
                ],
                'label' => 'Veuillez définir son statut :'
                ])
            // ->add('createdAt')
            // ->add('updatedAt')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
