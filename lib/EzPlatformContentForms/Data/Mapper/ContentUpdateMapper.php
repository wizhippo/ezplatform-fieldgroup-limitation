<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitation\EzPlatformContentForms\Data\Mapper;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\Data\Mapper\FormDataMapperInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentUpdateMapper implements FormDataMapperInterface
{
    /** @var \eZ\Publish\API\Repository\PermissionResolver */
    private $permissionResolver;

    /**
     * @param \eZ\Publish\API\Repository\PermissionResolver $permissionResolver
     */
    public function __construct(PermissionResolver $permissionResolver)
    {
        $this->permissionResolver = $permissionResolver;
    }

    /**
     * Maps a ValueObject from eZ content repository to a data usable as underlying form data (e.g. create/update
     * struct).
     *
     * @param \eZ\Publish\API\Repository\Values\Content\Content|\eZ\Publish\API\Repository\Values\ValueObject $contentDraft
     * @param array $params
     *
     * @return \EzSystems\EzPlatformContentForms\Data\Content\ContentUpdateData
     */
    public function mapToFormData(ValueObject $contentDraft, array $params = [])
    {
        $optionsResolver = new OptionsResolver();
        $this->configureOptions($optionsResolver);

        $params = $optionsResolver->resolve($params);
        $languageCode = $params['languageCode'];

        $data = new ContentUpdateData(['contentDraft' => $contentDraft]);
        $data->initialLanguageCode = $languageCode;

        $fields = $contentDraft->getFieldsByLanguage($languageCode);
        foreach ($params['contentType']->fieldDefinitions as $fieldDef) {
            if (!$this->permissionResolver->canUser('content_field', 'edit', $params['contentType'], [$fieldDef])) {
                continue;
            }

            $field = $fields[$fieldDef->identifier];
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => $field,
                'value' => $field->value,
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired(['languageCode', 'contentType'])
            ->setAllowedTypes('contentType', ContentType::class);
    }
}
