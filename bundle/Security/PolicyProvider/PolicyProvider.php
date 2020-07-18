<?php

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\Security\PolicyProvider;

use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Configuration\ConfigBuilderInterface;
use eZ\Bundle\EzPublishCoreBundle\DependencyInjection\Security\PolicyProvider\PolicyProviderInterface;

class PolicyProvider implements PolicyProviderInterface
{
    public function addPolicies(ConfigBuilderInterface $configBuilder): void
    {
        $configBuilder->addConfig([
            'content_field' => [
                'create' => ['FieldGroup'],
                'edit' => ['FieldGroup'],
                'read' => ['FieldGroup'],
            ],
        ]);
    }
}
