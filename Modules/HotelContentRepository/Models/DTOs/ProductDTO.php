<?php

namespace Modules\HotelContentRepository\Models\DTOs;

use Illuminate\Database\Eloquent\Collection;
use Modules\HotelContentRepository\Models\Product;

class ProductDTO
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

    public function __construct(
        private readonly ProductAffiliationDTO $productAffiliationDTO,
        private readonly ProductAgeRestrictionDTO $productAgeRestrictionDTO,
        private readonly ProductAttributeDTO $productAttributeDTO,
        private readonly ProductDescriptiveContentSectionDTO $productDescriptiveContentSectionDTO,
        private readonly ProductFeeTaxDTO $productFeeTaxDTO,
        private readonly ProductInformativeServiceDTO $productInformativeServiceDTO,
        private readonly ProductPromotionDTO $productPromotionDTO,
        private readonly KeyMappingDTO $keyMappingDTO,
        private readonly ImageGalleryDTO $imageGalleryDTO,
        private readonly ContactInformationDTO $contactInformationDTO,
        private readonly ProductCancellationPolicyDTO $productCancellationPolicyDTO,
        private readonly ProductDepositInformationDTO $productDepositInformationDTO,
    ) {}

    public function transform(Collection $products, bool $returnRelation = false)
    {
        return $products->map(function ($product) use ($returnRelation) {
            return $this->transformProduct($product, $returnRelation);
        })->all();
    }

    public function transformProduct(Product $product, bool $returnRelation = false)
    {
        $data = [
            'id' => $product->id,
            'vendor' => $product->vendor->name,
            'product_type' => $product->product_type,
            'name' => $product->name,
            'verified' => $product->verified,
            'content_source' => resolve(ContentSourceDTO::class)->transformContentSource($product->contentSource),
            'property_images_source' => resolve(ContentSourceDTO::class)->transformContentSource($product->propertyImagesSource),
            'default_currency' => $product->default_currency,
            'website' => $product->website,
            'location' => $product->location,
            'lat' => $product->lat,
            'lng' => $product->lng,
            'travel_agent_commission' => $product->travel_agent_commission,
            'affiliations' => $this->productAffiliationDTO->transform($product->affiliations),
            'age_restrictions' => $this->productAgeRestrictionDTO->transform($product->ageRestrictions),
            'attributes' => $this->productAttributeDTO->transform($product->attributes),
            'descriptive_contents' => $this->productDescriptiveContentSectionDTO->transform($product->descriptiveContentsSection),
            'fee_taxes' => $this->productFeeTaxDTO->transform($product->feeTaxes),
            'informative_services' => $this->productInformativeServiceDTO->transform($product->informativeServices),
            'promotions' => $this->productPromotionDTO->transform($product->promotions),
            'key_mappings' => $this->keyMappingDTO->transform($product->keyMappings),
            'galleries' => $this->imageGalleryDTO->transform($product->galleries),
            'contact_information' => $product->contactInformation ? $this->contactInformationDTO->transform($product->contactInformation) : null,
            'cancellation_policies' => $product->cancellationPolicies ? $this->productCancellationPolicyDTO->transform($product->cancellationPolicies) : null,
            'deposit_information' => $product->depositInformations ? $this->productDepositInformationDTO->transform($product->depositInformations) : null,
        ];

        if ($returnRelation && $product->product_type === 'hotel') {
            try {
                $hotelDTO = resolve(HotelDTO::class);
                $data['related'] = $hotelDTO->transformHotel($product->related);
            } catch (\Exception $e) {
                \Log::error('error related product', ['error' => $e->getMessage()]);
            }
        }

        return $data;
    }
}
