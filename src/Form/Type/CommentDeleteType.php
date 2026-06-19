<?php

/**
 * Comment delete type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for deleting Comment entity.
 */
class CommentDeleteType extends AbstractType
{
    /**
     * Builds the form for Comment entity.
     *
     * @param FormBuilderInterface $builder the form builder
     * @param array                $options the options for configuring the form
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('delete', SubmitType::class, [
                'label' => 'action.delete',
                'attr' => ['class' => 'btn btn-danger'],
            ]);
    }
}
