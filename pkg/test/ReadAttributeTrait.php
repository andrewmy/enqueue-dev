<?php

namespace Enqueue\Test;

trait ReadAttributeTrait
{
    public function readAttribute(object $object, string $attribute)
    {
        $refProperty = new \ReflectionProperty(get_class($object), $attribute);
        $refProperty->setAccessible(true);
        $value = $refProperty->getValue($object);
        $refProperty->setAccessible(false);

        return $value;
    }

    private function assertAttributeSame($expected, string $attribute, object $object): void
    {
        static::assertSame($expected, $this->readAttribute($object, $attribute));
    }
}
