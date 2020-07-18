<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\Form\Extension;

use EzSystems\EzPlatformContentForms\Form\Type\Content\ContentFieldType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentFieldTypeExtension extends AbstractTypeExtension
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('can_read_field', true);
    }

    public static function getExtendedTypes(): iterable
    {
        return [ContentFieldType::class];
    }
}
