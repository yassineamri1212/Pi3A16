<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class EnumType extends Type
{
    const ENUM = 'enum';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return "VARCHAR(255)"; // Treat ENUM as VARCHAR(255)
    }

    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
    {
        return $value; // No conversion needed
    }

    public function getName()
    {
        return self::ENUM;
    }
}
