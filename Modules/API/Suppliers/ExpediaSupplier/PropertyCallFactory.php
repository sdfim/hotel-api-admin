<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

class PropertyCallFactory
{
    private RapidClient $rapidClient;

    public function __construct(RapidClient $rapidClient)
    {
        $this->rapidClient = $rapidClient;
    }

    public function createPropertyPriceCall(array $properties): PropertyPriceCall
    {
        return new PropertyPriceCall($this->rapidClient, $properties);
    }

    public function createPropertyContentCall(array $properties): PropertyContentCall
    {
        return new PropertyContentCall($this->rapidClient, $properties);
    }
}
