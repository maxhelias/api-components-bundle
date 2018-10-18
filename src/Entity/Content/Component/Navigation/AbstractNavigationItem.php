<?php

namespace Silverback\ApiComponentBundle\Entity\Content\Component\Navigation;

use Doctrine\ORM\Mapping as ORM;
use Silverback\ApiComponentBundle\Entity\Content\Component\AbstractComponent;
use Silverback\ApiComponentBundle\Entity\Route\Route;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Class AbstractNavigationItem
 * @package Silverback\ApiComponentBundle\Entity\Navigation
 * @author Daniel West <daniel@silverback.is>
 */
abstract class AbstractNavigationItem extends AbstractComponent implements NavigationItemInterface
{
    /**
     * @ORM\Column()
     * @Groups({"layout", "content", "component"})
     * @var string
     */
    protected $label;

    /**
     * @ORM\ManyToOne(targetEntity="Silverback\ApiComponentBundle\Entity\Route\Route")
     * @ORM\JoinColumn(referencedColumnName="route", onDelete="CASCADE")
     * @Groups({"layout", "content", "component"})
     * @var null|Route
     */
    protected $route;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"layout", "content", "component"})
     * @var null|string
     */
    protected $fragment;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return AbstractNavigationItem
     */
    public function setLabel(string $label): AbstractNavigationItem
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return null|Route
     */
    public function getRoute(): ?Route
    {
        return $this->route;
    }

    /**
     * @param null|Route $route
     * @return AbstractNavigationItem
     */
    public function setRoute(?Route $route): AbstractNavigationItem
    {
        $this->route = $route;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFragment(): ?string
    {
        return $this->fragment;
    }

    /**
     * @param null|string $fragment
     * @return AbstractNavigationItem
     */
    public function setFragment(?string $fragment): AbstractNavigationItem
    {
        $this->fragment = $fragment;
        return $this;
    }
}