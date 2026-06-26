<?php

/**
 * User edit type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use App\Entity\User;

/**
 * Form type for editing user information.
 */
class UserEditType extends AbstractType
{
    /**
     * Builds the form for editing user information.
     *
     * @param FormBuilderInterface $builder the form builder
     * @param array                $options the options for configuring the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, [
                'label' => 'label.username',
            ])
            ->add('email', null, [
                'label' => 'label.email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.newpasswd',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(min: 6, max: 4096),
                ],
            ])
            ->add('roles', ChoiceType::class, [
                'label' => 'label.roles',
                'choices' => [
                    'role.user' => 'ROLE_USER',
                    'role.admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
    }

    /**
     * Configures options for the user edit form type.
     *
     * @param OptionsResolver $resolver the resolver for configuring form options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
