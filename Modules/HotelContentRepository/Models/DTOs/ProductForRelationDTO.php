<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Modules\HotelContentRepository\Models\Product;

class ProductForRelationDTO
{
    public $id;
    public $vendor;
    public $product_type;
    public $name;
    public $verified;
    public $content_source;
    public $property_images_source;
    public $default_currency;
    public $website;
    public $location;
    public $lat;
    public $lng;
    public $affiliations;
    public $age_restrictions;
    public $attributes;
    public $descriptive_contents_section;
    public $fee_taxes;
    public $informative_services;
    public $promotions;
    public $key_mappings;
    public $galleries;
    public $contact_information;
    public $travel_agent_commission;

    public function __construct(Product $product)
    {
        $this->id = $product->id;
        $this->vendor = $product->vendor->name;
        $this->product_type = $product->product_type;
        $this->name = $product->name;
        $this->verified = $product->verified;
        $this->content_source = new ContentSourceDTO($product->contentSource);
        $this->property_images_source = new ContentSourceDTO($product->propertyImagesSource);
        $this->default_currency = $product->default_currency;
        $this->website = $product->website;
        $this->location = $product->location;
        $this->lat = $product->lat;
        $this->lng = $product->lng;
        $this->travel_agent_commission = $product->travel_agent_commission;
        $this->affiliations = $product->affiliations->map(function ($affiliation) {
            return new ProductAffiliationDTO($affiliation);
        });
        $this->age_restrictions = $product->ageRestrictions->map(function ($ageRestriction) {
            return new ProductAgeRestrictionDTO($ageRestriction);
        });
        $this->attributes = $product->attributes->map(function ($attribute) {
            return new ProductAttributeDTO($attribute);
        });
        $this->descriptive_contents_section = $product->descriptiveContentsSection->map(function ($section) {
            return new ProductDescriptiveContentSectionDTO($section);
        });
        $this->fee_taxes = $product->feeTaxes->map(function ($feeTax) {
            return new ProductFeeTaxDTO($feeTax);
        });
        $this->informative_services = $product->informativeServices->map(function ($service) {
            return new ProductInformativeServiceDTO($service);
        });
        $this->promotions = $product->promotions->map(function ($promotion) {
            return new ProductPromotionDTO($promotion);
        });
        $this->key_mappings = $product->keyMappings->map(function ($keyMapping) {
            return new KeyMappingDTO($keyMapping);
        });
        $this->galleries = $product->galleries->map(function ($gallery) {
            return new ImageGalleryDTO($gallery);
        });
        $this->contact_information = $product->contactInformation->map(function ($contact) {
            return new ProductContactInformationDTO($contact);
        });
    }
}
