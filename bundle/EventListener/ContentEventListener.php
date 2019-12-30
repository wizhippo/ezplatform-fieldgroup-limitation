<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\EventListener;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentEvent;
use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\Core\Base\Exceptions\UnauthorizedException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentEventListener implements EventSubscriberInterface
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * @param \eZ\Publish\API\Repository\ContentService $contentService
     * @param \eZ\Publish\API\Repository\ContentTypeService $contentTypeService
     * @param \Symfony\Component\Form\FormFactoryInterface $formFactory
     * @param \eZ\Publish\Core\MVC\Symfony\Locale\UserLanguagePreferenceProviderInterface $languagePreferenceProvider
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(
        PermissionResolver $permissionResolver,
        ContentTypeService $contentTypeService
    ) {
        $this->permissionResolver = $permissionResolver;
        $this->contentTypeService = $contentTypeService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            BeforeCreateContentEvent::class => 'beforeCreateContent',
            BeforeUpdateContentEvent::class => 'beforeUpdateContent',
        ];
    }

    public function beforeCreateContent(BeforeCreateContentEvent $event)
    {
        $contentCreateStruct = $event->getContentCreateStruct();
        $contentType = $this->contentTypeService->loadContentType($contentCreateStruct->contentType->id);

        foreach ($contentCreateStruct->fields as $field) {
            $fieldDef = $contentType->getFieldDefinition($field->fieldDefIdentifier);
            if ($fieldDef) {
                if (!$this->permissionResolver->canUser('content_field', 'create', $contentCreateStruct, [$fieldDef])) {
                    throw new UnauthorizedException('content_field', 'create', ['fieldIdentifier' => $fieldIdentifier]);
                }
            }
        }
    }

    public function beforeUpdateContent(BeforeUpdateContentEvent $event)
    {
        $contentUpdateStruct = $event->getContentUpdateStruct();
        $contentType = $this->contentTypeService->loadContentType($event->getVersionInfo()->contentInfo->contentTypeId);

        foreach ($contentUpdateStruct->fields as $field) {
            $fieldDef = $contentType->getFieldDefinition($field->fieldDefIdentifier);
            if ($fieldDef) {
                if (!$this->permissionResolver->canUser('content_field', 'edit', $contentUpdateStruct, [$fieldDef])) {
                    throw new UnauthorizedException('content_field', 'edit', ['fieldIdentifier' => $fieldIdentifier]);
                }
            }
        }
    }
}
