<?php

namespace Silverback\ApiComponentBundle\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Silverback\ApiComponentBundle\Entity\Component\AbstractComponent;
use Silverback\ApiComponentBundle\Entity\Content\AbstractContent;
use Silverback\ApiComponentBundle\Entity\Content\ComponentLocation;
use Silverback\ApiComponentBundle\Entity\Layout\Layout;
use Silverback\ApiComponentBundle\Entity\Navigation\AbstractNavigation;
use Silverback\ApiComponentBundle\Entity\Navigation\AbstractNavigationItem;
use Silverback\ApiComponentBundle\Entity\Navigation\Route\Route;
use Symfony\Component\HttpFoundation\Request;

class ApiContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;

    public function __construct(SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * @param string $group
     * @param bool $normalization
     * @param null|string $operation
     * @return array
     */
    private function getGroups(string $group, bool $normalization, ?string $operation): array
    {
        $groups = [$group, $group . ($normalization ? '_read' : '_write')];
        if ($operation) {
            $groups[] = "${group}_${operation}";
        }
        return $groups;
    }

    /**
     * @param $className
     * @param $matchClassName
     * @return bool
     */
    private function matchClass($className, $matchClassName): bool
    {
        return $className === $matchClassName || is_subclass_of($className, $matchClassName);
    }

    /**
     * @param Request $request
     * @param bool $normalization
     * @param array|null $extractedAttributes
     * @return array
     * @throws \ApiPlatform\Core\Exception\RuntimeException
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes = null) : array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (isset($context['groups']) && \in_array('none', $context['groups'], true)) {
            return $context;
        }
        $subject = $request->attributes->get('_api_resource_class');
        $operation = $context['item_operation_name'] ?? null;
        $groups = [];
        if (
            $this->matchClass($subject, AbstractComponent::class) ||
            $this->matchClass($subject, AbstractNavigation::class) ||
            $this->matchClass($subject, ComponentLocation::class)
        ) {
            $groups[] = $this->getGroups('component', $normalization, $operation);
        }
        if (
            $this->matchClass($subject, AbstractNavigationItem::class)
        ) {
            $groups[] = $this->getGroups('component_item', $normalization, $operation);
        }
        if (
            $this->matchClass($subject, AbstractContent::class)
        ) {
            $groups[] = $this->getGroups('content', $normalization, $operation);
        }
        if (
            $this->matchClass($subject, Route::class)
        ) {
            $groups[] = $this->getGroups('route', $normalization, $operation);
        }
        if ($this->matchClass($subject, Layout::class)) {
            $groups[] = $this->getGroups('layout', $normalization, $operation);
        }
        if (\count($groups)) {
            if (!isset($context['groups'])) {
                $context['groups'] = ['default'];
            } else {
                $context['groups'][] = ['default'];
            }
            array_unshift($groups, $context['groups']);
            $context['groups'] = array_merge( /** @scrutinizer ignore-type */ ...$groups);
        }
        return $context;
    }
}
