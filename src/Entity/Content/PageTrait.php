<?php

namespace Silverback\ApiComponentBundle\Entity\Content;

use ApiPlatform\Core\Annotation\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Silverback\ApiComponentBundle\Entity\Layout\Layout;
use Silverback\ApiComponentBundle\Entity\Route\Route;
use Symfony\Component\Serializer\Annotation\Groups;

trait PageTrait
{
    /**
     * @ORM\Column()
     * @Groups({"content", "route", "component"})
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column()
     * @Groups({"content", "route"})
     * @var string
     */
    protected $metaDescription;

    /**
     * @ORM\ManyToOne(targetEntity="Silverback\ApiComponentBundle\Entity\Content\Page")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Groups({"route"})
     * @var null|Page
     */
    protected $parent;

    /**
     * @ORM\ManyToOne(targetEntity="Silverback\ApiComponentBundle\Entity\Layout\Layout")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @ApiProperty()
     * @Groups({"content","route"})
     * @var Layout|null
     */
    protected $layout;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getMetaDescription(): string
    {
        return $this->metaDescription ?: '';
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription(string $metaDescription): void
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return null|Page
     */
    public function getParent(): ?Page
    {
        return $this->parent;
    }

    /**
     * @param null|Page $parent
     */
    public function setParent(?Page $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return Layout|null
     */
    public function getLayout(): ?Layout
    {
        return $this->layout;
    }

    /**
     * @param Layout|null $layout
     */
    public function setLayout(?Layout $layout): void
    {
        $this->layout = $layout;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultRoute(): string
    {
        return $this->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultRouteName(): string
    {
        return $this->getTitle();
    }

    /**
     * @inheritdoc
     */
    public function getParentRoute(): ?Route
    {
        return $this->getParent() ? $this->getParent()->getRoutes()->first() : null;
    }
}