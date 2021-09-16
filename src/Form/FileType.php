<?php

declare(strict_types=1);

namespace PhpGuild\MediaObjectBundle\Form;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FileType.
 */
class FileType extends \Symfony\Component\Form\Extension\Core\Type\FileType
{
    /**
     * buildView
     *
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['thumbnail'] = $options['thumbnail'];
    }

    /**
     * configureOptions
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('thumbnail', null);
    }
}
