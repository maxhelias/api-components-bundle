<?php

namespace Silverback\ApiComponentBundle\Tests\Unit\Serializer;

use ApiPlatform\Core\Serializer\AbstractItemNormalizer;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Silverback\ApiComponentBundle\Entity\Component\AbstractComponent;
use Silverback\ApiComponentBundle\Entity\Component\Form\Form;
use Silverback\ApiComponentBundle\Entity\Component\Form\FormView;
use Silverback\ApiComponentBundle\Factory\Entity\Component\Form\FormViewFactory;
use Silverback\ApiComponentBundle\Imagine\FileSystemLoader;
use Silverback\ApiComponentBundle\Serializer\ApiNormalizer;
use Silverback\ApiComponentBundle\Tests\TestBundle\Entity\FileComponent;
use Silverback\ApiComponentBundle\Tests\TestBundle\Form\TestType;
use Symfony\Component\Serializer\Serializer;

class ApiNormalizerTest extends TestCase
{
    /**
     * @var MockObject|AbstractItemNormalizer
     */
    private $normalizerInterfaceMock;
    /**
     * @var MockObject|CacheManager
     */
    private $cacheManagerMock;
    /**
     * @var MockObject|FormViewFactory
     */
    private $formViewFactoryMock;
    /**
     * @var ApiNormalizer
     */
    private $apiNormalizer;
    /**
     * @var string
     */
    private $filePath = __DIR__ . '/../../app/public/images/testImage.jpg';
    /**
     * @var MockObject|FileSystemLoader
     */
    private $fileSystemLoaderMock;

    public function setUp()
    {
        $this->normalizerInterfaceMock = $this->getMockBuilder(AbstractItemNormalizer::class)->disableOriginalConstructor()->getMock();
        $this->cacheManagerMock = $this->getMockBuilder(CacheManager::class)->disableOriginalConstructor()->getMock();
        $this->formViewFactoryMock = $this->getMockBuilder(FormViewFactory::class)->disableOriginalConstructor()->getMock();
        $this->fileSystemLoaderMock = $this->getMockBuilder(FileSystemLoader::class)->disableOriginalConstructor()->getMock();
        $this->apiNormalizer = new ApiNormalizer(
            $this->normalizerInterfaceMock,
            $this->cacheManagerMock,
            $this->formViewFactoryMock,
            $this->fileSystemLoaderMock
        );
    }

    public function test_supports_normalizer(): void
    {
        $args = [[], null];
        $this->normalizerInterfaceMock
            ->expects($this->once())
            ->method('supportsNormalization')
            ->with(...$args)
            ->willReturn(true)
        ;
        $this->assertTrue($this->apiNormalizer->supportsNormalization(...$args));
    }

    public function test_supports_denormalization(): void
    {
        $args = [[], null];
        $this->normalizerInterfaceMock
            ->expects($this->once())
            ->method('supportsDenormalization')
            ->with(...$args)
            ->willReturn(true)
        ;
        $this->assertTrue($this->apiNormalizer->supportsDenormalization(...$args));
    }

    public function test_imagine_supported_file(): void
    {
        $this->assertFalse($this->apiNormalizer->isImagineSupportedFile('not_a_file'));
        $this->assertFalse($this->apiNormalizer->isImagineSupportedFile('dummyfile.txt'));
        $this->assertFalse($this->apiNormalizer->isImagineSupportedFile('images/apiPlatform.svg'));
        $this->assertTrue($this->apiNormalizer->isImagineSupportedFile($this->filePath));
    }

    public function test_set_serializer(): void
    {
        $serializer = new Serializer();
        $this->normalizerInterfaceMock
            ->expects($this->once())
            ->method('setSerializer')
            ->with($serializer)
        ;
        $this->apiNormalizer->setSerializer($serializer);
    }

    public function test_denormalize(): void
    {
        $abstractComponentParentMock = $this->getMockBuilder(AbstractComponent::class)->getMock();
        $abstractComponentMock = $this->getMockBuilder(AbstractComponent::class)->getMock();
        $abstractComponentMock
            ->expects($this->once())
            ->method('getParent')
            ->willReturn($abstractComponentParentMock)
        ;
        $abstractComponentMock
            ->expects($this->once())
            ->method('addToParentComponent')
            ->with($abstractComponentParentMock)
        ;

        $args = [[], $abstractComponentMock, null];
        $this->normalizerInterfaceMock
            ->expects($this->once())
            ->method('denormalize')
            ->with(...$args)
            ->willReturn($abstractComponentMock)
        ;
        $this->apiNormalizer->denormalize(...array_merge($args, [['allow_extra_attributes' => false]]));
    }

    public function test_normalize_file(): void
    {
        $this->fileSystemLoaderMock
            ->expects($this->once())
            ->method('getImaginePath')
            ->with($this->filePath)
            ->willReturn($this->filePath)
        ;

        $fileComponent = new FileComponent();
        $fileComponent->setFilePath($this->filePath);

        foreach (FileComponent::getImagineFilters() as $returnKey => $filter) {
            $this->cacheManagerMock
                ->expects($this->once())
                ->method('getBrowserPath')
                ->with($this->filePath, $filter)
                ->willReturn(sprintf('http://website.com/%s/%s', $filter, $this->filePath))
            ;
        }

        $this->normalizerInterfaceMock
            ->expects($this->once())
            ->method('normalize')
            ->with($fileComponent)
            ->willReturn([])
        ;

        $data = $this->apiNormalizer->normalize($fileComponent);
        $this->assertEquals(100, $data['width']);
        $this->assertEquals(100, $data['height']);
        foreach (FileComponent::getImagineFilters() as $returnKey => $filter) {
            $this->assertEquals(sprintf('/%s/%s', $filter, $this->filePath), $data[$returnKey]);
        }
    }

    public function test_normalize_form(): void
    {
        $formEntity = new Form();
        $formEntity->setClassName(TestType::class);

        /** @var MockObject|\Symfony\Component\Form\FormView $formViewMock */
        $formViewMock = $this->getMockBuilder(\Symfony\Component\Form\FormView::class)->getMock();
        $formViewMock
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator())
        ;
        $formView = new FormView($formViewMock);

        $this->formViewFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($formEntity)
            ->willReturn($formView)
        ;

        $data = $this->apiNormalizer->normalize($formEntity);
        $this->assertEquals($formView, $data['form']);
    }
}