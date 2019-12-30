<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitation\Core\Limitation;

use eZ\Publish\API\Repository\Exceptions\NotImplementedException;
use eZ\Publish\API\Repository\Values\User\Limitation as APILimitationValue;
use eZ\Publish\API\Repository\Values\User\UserReference as APIUserReference;
use eZ\Publish\API\Repository\Values\ValueObject;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList;
use eZ\Publish\SPI\Limitation\Type as SPILimitationTypeInterface;
use Wizhippo\WizhippoFieldGroupLimitation\API\Repository\Values\User\Limitation\FieldGroupLimitation as APIFieldGroupLimitation;

class FieldGroupLimitationType implements SPILimitationTypeInterface
{
    /** @var \eZ\Publish\Core\Helper\FieldsGroups\FieldsGroupsList */
    private $fieldsGroupsList;

    public function __construct(FieldsGroupsList $fieldsGroupsList)
    {
        $this->fieldsGroupsList = $fieldsGroupsList;
    }

    public function acceptValue(APILimitationValue $limitationValue): void
    {
        if (!$limitationValue instanceof APIFieldGroupLimitation) {
            throw new InvalidArgumentType('$limitationValue', APIFieldGroupLimitation::class, $limitationValue);
        } elseif (!\is_array($limitationValue->limitationValues)) {
            throw new InvalidArgumentType('$limitationValue->limitationValues', 'array',
                $limitationValue->limitationValues);
        }

        foreach ($limitationValue->limitationValues as $key => $value) {
            if (!\is_string($value)) {
                throw new InvalidArgumentType("\$limitationValue->limitationValues[{$key}]", 'string', $value);
            }
        }
    }

    public function validate(APILimitationValue $limitationValue): array
    {
        $validationErrors = [];
        $existingGroups = array_keys($this->fieldsGroupsList->getGroups());
        $missingGroups = array_diff(
            $limitationValue->limitationValues,
            $existingGroups
        );

        if (!empty($missingGroups)) {
            $validationErrors[] = new ValidationError(
                "limitationValues[] => '%fieldGroups%' do not exist",
                null,
                [
                    'fieldGroups' => implode(', ', $missingGroups),
                ]
            );
        }

        return $validationErrors;
    }

    public function buildValue(array $limitationValues): APILimitationValue
    {
        return new APIFieldGroupLimitation(['limitationValues' => $limitationValues]);
    }

    public function evaluate(
        APILimitationValue $value,
        APIUserReference $currentUser,
        ValueObject $object,
        ?array $targets = null
    ): ?bool {
        if (null == $targets) {
            $targets = [];
        }

        $fieldGroups = $value->limitationValues;
        foreach ($targets as $target) {
            if (\in_array($target->fieldGroup, $fieldGroups)) {
                return true;
            }
        }

        return false;
    }

    public function getCriterion(APILimitationValue $value, APIUserReference $currentUser)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function valueSchema()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
