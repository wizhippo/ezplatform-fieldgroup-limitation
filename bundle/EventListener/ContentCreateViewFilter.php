<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\EventListener;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData;
use EzSystems\EzPlatformContentForms\Form\Type\Content\ContentEditType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Wizhippo\WizhippoFieldGroupLimitation\EzPlatformContentForms\Data\Mapper\ContentCreateMapper;

class ContentCreateViewFilter implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\LocationService */
    private $locationService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * @param \eZ\Publish\API\Repository\LocationService $locationService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(
        LocationService $locationService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider,
        PermissionResolver $permissionResolver
    ) {
        $this->locationService = $locationService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
        $this->permissionResolver = $permissionResolver;
    }

    public static function getSubscribedEvents()
    {
        // After native handler
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => ['handleContentCreateForm', -1]];
    }

    /**
     * @param \eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent $event
     *
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function handleContentCreateForm(FilterViewBuilderParametersEvent $event)
    {
        if ('ez_content_edit:createWithoutDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');

        $contentType = $this->contentTypeService->loadContentTypeByIdentifier(
            $request->attributes->get('contentTypeIdentifier'),
            $this->languagePreferenceProvider->getPreferredLanguages()
        );
        $location = $this->locationService->loadLocation($request->attributes->get('parentLocationId'));

        $contentCreateData = $this->resolveContentCreateData($contentType, $location, $languageCode);
        $form = $this->resolveContentCreateForm(
            $contentCreateData,
            $languageCode
        );

        $event->getParameters()->add(['form' => $form->handleRequest($request)]);
    }

    /**
     * @param \eZ\Publish\API\Repository\Values\ContentType\ContentType $contentType
     * @param \eZ\Publish\API\Repository\Values\Content\Location $location
     * @param string $languageCode
     *
     * @return \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData
     */
    private function resolveContentCreateData(
        ContentType $contentType,
        Location $location,
        string $languageCode
    ): ContentCreateData {
        $contentCreateMapper = new ContentCreateMapper($this->permissionResolver);

        return $contentCreateMapper->mapToFormData(
            $contentType,
            [
                'mainLanguageCode' => $languageCode,
                'parentLocation' => $this->locationService->newLocationCreateStruct($location->id, $contentType),
            ]
        );
    }

    /**
     * @param \EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData $contentCreateData
     * @param string $languageCode
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    private function resolveContentCreateForm(
        ContentCreateData $contentCreateData,
        string $languageCode
    ): FormInterface {
        return $this->formFactory->create(ContentEditType::class, $contentCreateData, [
            'languageCode' => $languageCode,
            'mainLanguageCode' => $languageCode,
            'drafts_enabled' => true,
        ]);
    }
}
