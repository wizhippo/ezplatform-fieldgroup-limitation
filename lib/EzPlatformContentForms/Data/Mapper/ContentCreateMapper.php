<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitation\EzPlatformContentForms\Data\Mapper;

use eZ\Publish\API\Repository\PermissionResolver;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\API\Repository\Values\ValueObject;
use EzSystems\EzPlatformContentForms\Data\Content\ContentCreateData;
use EzSystems\EzPlatformContentForms\Data\Content\FieldData;
use EzSystems\EzPlatformContentForms\Data\Mapper\FormDataMapperInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentCreateMapper implements FormDataMapperInterface
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

    public function mapToFormData(ValueObject $contentType, array $params = [])
    {
        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $params = $resolver->resolve($params);

        $data = new ContentCreateData([
            'contentType' => $contentType,
            'mainLanguageCode' => $params['mainLanguageCode']
        ]);
        $data->addLocationStruct($params['parentLocation']);
        foreach ($contentType->fieldDefinitions as $fieldDef) {
            if (!$this->permissionResolver->canUser('content_field', 'create', $contentType, [$fieldDef])) {
                continue;
            }
            $data->addFieldData(new FieldData([
                'fieldDefinition' => $fieldDef,
                'field' => new Field([
                    'fieldDefIdentifier' => $fieldDef->identifier,
                    'languageCode' => $params['mainLanguageCode'],
                ]),
                'value' => $fieldDef->defaultValue,
            ]));
        }

        return $data;
    }

    private function configureOptions(OptionsResolver $optionsResolver)
    {
        $optionsResolver
            ->setRequired(['mainLanguageCode', 'parentLocation'])
            ->setAllowedTypes('parentLocation', '\eZ\Publish\API\Repository\Values\Content\LocationCreateStruct');
    }
}
