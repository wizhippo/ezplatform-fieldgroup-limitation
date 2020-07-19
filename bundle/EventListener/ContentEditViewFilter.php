<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\EventListener;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface;
use eZ\Publish\Core\MVC\Symfony\View\Event\FilterViewBuilderParametersEvent;
use eZ\Publish\Core\MVC\Symfony\View\ViewEvents;
use EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData;
use EzSystems\EzPlatformContentForms\Form\Type\Content\ContentEditType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Wizhippo\WizhippoFieldGroupLimitation\EzPlatformContentForms\Data\Mapper\ContentUpdateMapper;

class ContentEditViewFilter implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    private $formFactory;

    /** @var \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface */
    private $languagePreferenceProvider;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        FormFactoryInterface $formFactory,
        UserLanguagePreferenceProviderInterface $languagePreferenceProvider,
        PermissionResolver $permissionResolver
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->formFactory = $formFactory;
        $this->languagePreferenceProvider = $languagePreferenceProvider;
        $this->permissionResolver = $permissionResolver;
    }

    public static function getSubscribedEvents()
    {
        // After native handler
        return [ViewEvents::FILTER_BUILDER_PARAMETERS => ['handleContentEditForm', -1]];
    }

    public function handleContentEditForm(FilterViewBuilderParametersEvent $event)
    {
        if ('ez_content_edit:editVersionDraftAction' !== $event->getParameters()->get('_controller')) {
            return;
        }

        $request = $event->getRequest();
        $languageCode = $request->attributes->get('language');
        $contentDraft = $this->contentService->loadContent(
            $request->attributes->getInt('contentId'),
            [$languageCode], // @todo: rename to languageCode in 3.0
            $request->attributes->getInt('versionNo')
        );

        $contentType = $this->contentTypeService->loadContentType(
            $contentDraft->contentInfo->contentTypeId,
            $this->languagePreferenceProvider->getPreferredLanguages()
        );

        $contentUpdate = $this->resolveContentEditData($contentDraft, $languageCode, $contentType);
        $form = $this->resolveContentEditForm(
            $contentUpdate,
            $languageCode,
            $contentDraft
        );

        $event->getParameters()->add([
            'form' => $form->handleRequest($request),
            'validate' => (bool)$request->get('validate', false),
        ]);
    }

    private function resolveContentEditData(
        Content $content,
        string $languageCode,
        ContentType $contentType
    ): ContentUpdateData {
        $contentUpdateMapper = new ContentUpdateMapper($this->permissionResolver);

        return $contentUpdateMapper->mapToFormData($content, [
            'languageCode' => $languageCode,
            'contentType' => $contentType,
        ]);
    }

    private function resolveContentEditForm(
        ContentUpdateData $contentUpdate,
        string $languageCode,
        Content $content
    ): FormInterface {
        return $this->formFactory->create(
            ContentEditType::class,
            $contentUpdate,
            [
                'languageCode' => $languageCode,
                'mainLanguageCode' => $content->contentInfo->mainLanguageCode,
                'content' => $content,
                'contentUpdateStruct' => $contentUpdate,
                'drafts_enabled' => true,
            ]
        );
    }
}
