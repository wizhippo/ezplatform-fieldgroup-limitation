<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Wizhippo\WizhippoFieldGroupLimitationBundle\Security\PolicyProvider\PolicyProvider;

class WizhippoFieldGroupLimitationBundle extends Bundle
{
    function build(ContainerBuilder $container)
    {
        parent::build($container);

        $eZExtension = $container->getExtension('ezpublish');
        $eZExtension->addPolicyProvider(new PolicyProvider());
    }
}
