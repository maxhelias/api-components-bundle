<?php

namespace Silverback\ApiComponentBundle\Factory\Entity\Content\Component;

use Silverback\ApiComponentBundle\Entity\Content\Component\AbstractComponent;
use Silverback\ApiComponentBundle\Entity\Content\Component\ComponentLocation;
use Silverback\ApiComponentBundle\Factory\Entity\AbstractFactory;

abstract class AbstractComponentFactory extends AbstractFactory
{
    /**
     * @param AbstractComponent $component
     * @param array|null $ops
     */
    protected function init($component, ?array $ops = null): void
    {
        parent::init($component, $ops);
        if (
            $this->ops['parentContent'] &&
            !$component->isContentComponent($this->ops['parentContent'])
        ) {
            $location = new ComponentLocation($this->ops['parentContent'], $component);
            $component->addLocation($location);
            $this->manager->persist($location);
        }
    }

    protected static function getIgnoreOps(): array
    {
        return [
            'parentContent'
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defaultOps(): array
    {
        return [
            'className' => null,
            'parentComponent' => null,
            'parentContent' => null
        ];
    }
}
