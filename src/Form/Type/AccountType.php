<?php

/**
 * Account type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormError;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraints\Length;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

/**
 * Form type for managing user account information.
 */
class AccountType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param Security $security the security component for user management
     */
    public function __construct(private readonly Security $security)
    {
    }

    /**
     * Builds the form with fields and event listeners.
     *
     * @param FormBuilderInterface $builder the form builder
     * @param array                $options the options for configuring the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                'label' => 'label.username',
            ])
            ->add('email', EmailType::class, [
                'label' => 'label.email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'label.passwd',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length(min: 6, max: 4096),
                ],
            ])
            ->add('repeatPassword', PasswordType::class, [
                'label' => 'label.repeatpasswd',
                'mapped' => false,
                'required' => false,
            ]);

        if ($options['is_admin']) {
            $builder->add('roles', ChoiceType::class, [
                'label' => 'label.roles',
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                ],
                'multiple' => true,
                'expanded' => true,
            ]);
        }

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event): void {
                $form = $event->getForm();
                $data = $event->getData();

                $plainPassword = $form->get('plainPassword')->getData();
                $repeatPassword = $form->get('repeatPassword')->getData();

                if ($plainPassword !== $repeatPassword) {
                    $form->get('repeatPassword')->addError(new FormError('The password fields must match.'));
                }
            }
        );
    }

    /**
     * Configures options for the form type.
     *
     * @param OptionsResolver $resolver the resolver for configuring form options
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'is_admin' => false,
        ]);
    }
}
