<?php

namespace Modules\HotelContentRepository\Models\Transformers;

use League\Fractal\TransformerAbstract;
use Modules\HotelContentRepository\Models\Hotel;

class HotelTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = [
        'rooms',
        'webFinders',
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

    public function transform(Hotel $hotel)
    {
        return [
            'id' => $hotel->id,
            'name' => $hotel->name,
            'weight' => $hotel->weight,
            'type' => $hotel->type,
            'verified' => (bool) $hotel->verified,
            'address' => $hotel->address,
            'star_rating' => $hotel->star_rating,
            'num_rooms' => $hotel->num_rooms,
            'featured' => (bool) $hotel->featured,
            'location' => $hotel->location,
            'content_source' => $hotel->contentSource->name,
            'room_images_source' => $hotel->roomImagesSource->name,
            'property_images_source' => $hotel->propertyImagesSource->name,
            'travel_agent_commission' => $hotel->travel_agent_commission,
            'hotel_board_basis' => $hotel->hotel_board_basis,
            'default_currency' => $hotel->default_currency,
        ];
    }

    public function includeContentSource(Hotel $hotel)
    {
        return $this->item($hotel->contentSource, new ContentSourceTransformer());
    }

    public function includeWebFinders(Hotel $hotel)
    {
        return $this->collection($hotel->webFinders, new WebFinderTransformer());
    }

    public function includeContactInformation(Hotel $hotel)
    {
        return $this->collection($hotel->contactInformation, new ContactInformationTransformer());
    }

    public function includeAffiliations(Hotel $hotel)
    {
        return $this->collection($hotel->affiliations, new AffiliationTransformer());
    }

    public function includeAttributes(Hotel $hotel)
    {
        return $this->collection($hotel->attributes, new AttributeTransformer());
    }

    public function includeDescriptiveContentsSection(Hotel $hotel)
    {
        return $this->collection($hotel->descriptiveContentsSection, new DescriptiveContentsSectionTransformer());
    }

    public function includeFeeTaxes(Hotel $hotel)
    {
        return $this->collection($hotel->feeTaxes, new FeeTaxTransformer());
    }

    public function includeInformativeServices(Hotel $hotel)
    {
        return $this->collection($hotel->informativeServices, new InformativeServiceTransformer());
    }

    public function includePromotions(Hotel $hotel)
    {
        return $this->collection($hotel->promotions, new PromotionTransformer());
    }

    public function includeRooms(Hotel $hotel)
    {
        return $this->collection($hotel->rooms, new RoomTransformer());
    }

    public function includeKeyMappings(Hotel $hotel)
    {
        return $this->collection($hotel->keyMappings, new KeyMappingTransformer());
    }

    public function includeGalleries(Hotel $hotel)
    {
        return $this->collection($hotel->galleries, new GalleryTransformer());
    }
}
