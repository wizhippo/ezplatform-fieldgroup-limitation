<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitation\EzPlatformAdminUi\Limitation\Mapper;

use eZ\Publish\API\Repository\Values\User\Limitation;
use eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList;
use EzSystems\EzPlatformAdminUi\Limitation\LimitationValueMapperInterface;
use EzSystems\EzPlatformAdminUi\Limitation\Mapper\MultipleSelectionBasedMapper;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class FieldGroupFormMapper extends MultipleSelectionBasedMapper implements LimitationValueMapperInterface
{
    use LoggerAwareTrait;

    /** @var \eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsList;

    public function __construct(FieldsGroupsList $fieldsGroupsList)
    {
        $this->fieldsGroupsList = $fieldsGroupsList;
        $this->logger = new NullLogger();
    }

    protected function getSelectionChoices()
    {
        return $this->fieldsGroupsList->getGroups();
    }

    public function mapLimitationValue(Limitation $limitation)
    {
        $values = [];
        $fieldGroups = $this->fieldsGroupsList->getGroups();

        foreach ($limitation->limitationValues as $fieldGroupIdentifier) {
            if (isset($fieldGroups[$fieldGroupIdentifier])) {
                $values[] = $fieldGroups[$fieldGroupIdentifier];
            } else {
                $this->logger->error(sprintf('Could not map limitation value: FieldGroup with identifier = %s not found',
                    $fieldGroupIdentifier));
            }
        }

        return $values;
    }
}
