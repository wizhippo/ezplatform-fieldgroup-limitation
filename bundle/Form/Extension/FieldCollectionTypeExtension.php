<?php

declare(strict_types=1);

namespace Wizhippo\WizhippoFieldGroupLimitationBundle\Form\Extension;

use EzSystems\EzPlatformContentForms\Form\Type\Content\FieldCollectionType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class FieldCollectionTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $form = $event->getForm();

            /** @var \Symfony\Component\Form\Form $child */
            foreach ($form as $name => $child) {
                if (!$child->getConfig()->getOption('can_read_field')) {
                    $form->remove($name);
                }
            }
        });
    }

    public static function getExtendedTypes(): iterable
    {
        return [FieldCollectionType::class];
    }
}
