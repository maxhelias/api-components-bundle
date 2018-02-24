<?php

namespace Silverback\ApiComponentBundle\Factory\Entity\Component\Form;

use Silverback\ApiComponentBundle\Entity\Component\Form\Form;
use Silverback\ApiComponentBundle\Entity\Component\Form\FormView;
use Silverback\ApiComponentBundle\Factory\Form\FormFactory as ACBFormFactory;

class FormViewFactory
{
    /**
     * @var ACBFormFactory
     */
    private $formFactory;

    public function __construct(
        ACBFormFactory $formFactory
    ) {
        $this->formFactory = $formFactory;
    }

    /**
     * @param Form $component
     * @return FormView
     */
    public function create(Form $component): FormView
    {
        $form = $this->formFactory->create($component);
        return new FormView($form->createView());
    }
}