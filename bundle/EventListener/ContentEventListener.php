<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\EventListener;

use eZ\Publish\API\Repository\Events\Content\BeforeCreateContentEvent;
use eZ\Publish\API\Repository\Events\Content\BeforeUpdateContentEvent;
use eZ\Publish\API\Repository\PermissionResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentEventListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
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
        $contentType = $contentCreateStruct->contentType;

        foreach ($contentCreateStruct->fields as $name => $field) {
            $fieldDef = $contentType->getFieldDefinition($field->fieldDefIdentifier);
            if ($fieldDef) {
                if (!$this->permissionResolver->canUser('content_field', 'create', $contentType, [$fieldDef])) {
                    unset($contentCreateStruct->fields[$name]);
                }
            }
        }
    }

    public function beforeUpdateContent(BeforeUpdateContentEvent $event)
    {
        $contentUpdateStruct = $event->getContentUpdateStruct();
        $contentType = $event->getContent()->getContentType();

        foreach ($contentUpdateStruct->fields as $name => $field) {
            $fieldDef = $contentType->getFieldDefinition($field->fieldDefIdentifier);
            if ($fieldDef) {
                if (!$this->permissionResolver->canUser('content_field', 'edit', $contentType, [$fieldDef])) {
                    unset($contentUpdateStruct->fields[$name]);
                }
            }
        }
    }
}
