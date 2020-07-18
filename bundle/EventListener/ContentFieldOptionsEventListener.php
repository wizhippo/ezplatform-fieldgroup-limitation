<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\EventListener;

use eZ\Publish\API\Repository\PermissionResolver;
use EzSystems\EzPlatformContentForms\Event\ContentCreateFieldOptionsEvent;
use EzSystems\EzPlatformContentForms\Event\ContentFormEvents;
use EzSystems\EzPlatformContentForms\Event\ContentUpdateFieldOptionsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContentFieldOptionsEventListener implements EventSubscriberInterface
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    public static function getSubscribedEvents()
    {
        return [
            ContentFormEvents::CONTENT_CREATE_FIELD_OPTIONS => 'onContentCreateFieldOptionsEvent',
            ContentFormEvents::CONTENT_EDIT_FIELD_OPTIONS => 'onContentUpdateFieldOptionsEvent',
        ];
    }

    public function onContentCreateFieldOptionsEvent(ContentCreateFieldOptionsEvent $event)
    {
        $canCreate = $this->permissionResolver->canUser('content_field', 'create',
            $event->getContentCreateStruct()->contentType, [$event->getFieldData()->field]);
        $canRead = $this->permissionResolver->canUser('content_field', 'read',
            $event->getContentCreateStruct()->contentType, [$event->getFieldData()->field]);

        if ($canCreate) {
            $canRead = true;
        }

        if (!$canCreate || !$canRead) {
            $event->setOption('disabled', true);
        }

        if (!$canRead) {
            $event->setOption('can_read_field', false);
        }
    }

    public function onContentUpdateFieldOptionsEvent(ContentUpdateFieldOptionsEvent $event)
    {
        $canEdit = $this->permissionResolver->canUser('content_field', 'edit',
            $event->getContent()->getContentType(), [$event->getFieldData()->field]);
        $canRead = $this->permissionResolver->canUser('content_field', 'read',
            $event->getContent()->getContentType(), [$event->getFieldData()->field]);

        if ($canEdit) {
            $canRead = true;
        }

        if (!$canEdit || !$canRead) {
            $event->setOption('disabled', true);
        }

        if (!$canRead) {
            $event->setOption('can_read_field', false);
        }
    }
}
