<?php

namespace Modules\API\Suppliers\ExpediaSupplier;

class PropertyCallFactory
{
    /**
     * @var RapidClient
     */
    private RapidClient $rapidClient;

    /**
     * @param RapidClient $rapidClient
     */
    public function __construct(RapidClient $rapidClient)
    {
        $this->rapidClient = $rapidClient;
    }

    /**
     * @param array $properties
     * @return PropertyPriceCall
     */
    public function createPropertyPriceCall(array $properties): PropertyPriceCall
    {
        return new PropertyPriceCall($this->rapidClient, $properties);
    }

    /**
     * @param array $properties
     * @return PropertyContentCall
     */
    public function createPropertyContentCall(array $properties): PropertyContentCall
    {
        return new PropertyContentCall($this->rapidClient, $properties);
    }
}
