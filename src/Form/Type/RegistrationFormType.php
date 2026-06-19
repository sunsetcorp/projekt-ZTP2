<?php

/**
 *  Registration form type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Form type for user registration.
 */
class RegistrationFormType extends AbstractType
{
    /**
     * Builds the registration form.
     *
     * @param FormBuilderInterface $builder the form builder
     * @param array                $options the options for configuring the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'label.email',
                'constraints' => [
                    new NotBlank(
                    ),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'label.username',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'label.passwd',
                ],
                'second_options' => [
                    'label' => 'label.repeatpasswd',
                ],
                'invalid_message' => 'The password fields must match.',
                'constraints' => [
                    new NotBlank(),
                ],
            ]);
    }

    /**
     * Configures options for the registration form type.
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
