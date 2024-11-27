<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\Product;

class ProductTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'galleries',
        'affiliations',
        'attributes',
        'contentSource',
        'contactInformation',
        'descriptiveContentsSection',
        'feeTaxes',
        'informativeServices',
        'promotions',
        'keyMappings',
    ];

    public function transform(Product $product)
    {
        return [
            'id' => $product->id,
            'vendor_id' => $product->vendor_id,
            'product_type' => $product->product_type,
            'name' => $product->name,
            'verified' => $product->verified,
            'content_source_id' => $product->content_source_id,
            'property_images_source_id' => $product->property_images_source_id,
            'lat' => $product->lat,
            'lng' => $product->lng,
            'default_currency' => $product->default_currency,
            'website' => $product->website,
            'related_id' => $product->related_id,
            'related_type' => $product->related_type,
        ];
    }

    public function includeContentSource(Product $product)
    {
        return $this->item($product->contentSource, new ContentSourceTransformer());
    }

    public function includeContactInformation(Product $product)
    {
        if ($product->contactInformation !== null) {
            return $this->collection($product->contactInformation, new ContactInformationTransformer());
        }
        return $this->null();
    }

    public function includeAffiliations(Product $product)
    {
        return $this->collection($product->affiliations, new AffiliationTransformer());
    }

    public function includeAttributes(Product $product)
    {
        return $this->collection($product->attributes, new AttributeTransformer());
    }

    public function includeDescriptiveContentsSection(Product $product)
    {
        return $this->collection($product->descriptiveContentsSection, new DescriptiveContentsSectionTransformer());
    }

    public function includeFeeTaxes(Product $product)
    {
        return $this->collection($product->feeTaxes, new FeeTaxTransformer());
    }

    public function includeInformativeServices(Product $product)
    {
        return $this->collection($product->informativeServices, new InformativeServiceTransformer());
    }

    public function includePromotions(Product $product)
    {
        return $this->collection($product->promotions, new PromotionTransformer());
    }

    public function includeKeyMappings(Product $product)
    {
        return $this->collection($product->keyMappings, new KeyMappingTransformer());
    }

    public function includeGalleries(Product $product)
    {
        return $this->collection($product->galleries, new GalleryTransformer());
    }
}
