<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitation\API\Repository\Values\User\Limitation;

use eZ\Publish\API\Repository\Values\User\Limitation as APILimitation;

class FieldGroupLimitation extends APILimitation
{
    public function getIdentifier(): string
    {
        return 'FieldGroup';
    }
}
