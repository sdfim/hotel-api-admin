<?php

namespace Modules\HotelContentRepository\Models\DTOs;

class HotelDTO
{
    public $id;
    public $name;
    public $weight;
    public $type;
    public $verified;
    public $address;
    public $star_rating;
    public $num_rooms;
    public $featured;
    public $location;
    public $content_source;
    public $room_images_source;
    public $property_images_source;
    public $travel_agent_commission;
    public $hotel_board_basis;
    public $default_currency;
    public $affiliations;
    public $attributes;
    public $descriptive_contents_section;
    public $fee_taxes;
    public $informational_service;
    public $promotions;
    public $rooms;
    public $key_mappings;
    public $galleries;
    public $contact_information;
    public $website_search_generation;

    public function __construct($hotel)
    {
        $this->id = $hotel->id;
        $this->name = $hotel->name;
        $this->weight = $hotel->weight;
        $this->type = $hotel->type;
        $this->verified = (bool) $hotel->verified;
        $this->address = $hotel->address;
        $this->star_rating = $hotel->star_rating;
        $this->num_rooms = $hotel->num_rooms;
        $this->featured = (bool) $hotel->featured;
        $this->location = $hotel->location;
        $this->content_source = $hotel->contentSource->name;
        $this->room_images_source = $hotel->roomImagesSource->name;
        $this->property_images_source = $hotel->propertyImagesSource->name;
        $this->travel_agent_commission = $hotel->travel_agent_commission;
        $this->hotel_board_basis = $hotel->hotel_board_basis;
        $this->default_currency = $hotel->default_currency;
        $this->website_search_generation = $hotel->webFinders->map(function ($webFinder) {
            return [
                'finder' => $webFinder->finder,
                'example' => $webFinder->example,
                'type' => $webFinder?->type,
                ];
        });
        $this->contact_information = $hotel->contactInformation->map(function ($contact) {
            return [
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'job_descriptions' => $contact->contactInformations->map(function ($job) {
                    return [
                        'name' => $job->name,
                    ];
                }),
            ];
        });
        $this->affiliations = $hotel->affiliations->map(function ($affiliation) {
            return [
                'affiliation_name' => $affiliation->affiliation_name,
                'combinable' => $affiliation->combinable,
            ];
        });
        $this->attributes = $hotel->attributes->map(function ($attribute) {
            return $attribute->attribute->name;
//            return [
//                'name' => $attribute->attribute->name,
//                'attribute_value' => $attribute->attribute->attribute_value,
//            ];
        });
        $this->descriptive_contents_section = $hotel->descriptiveContentsSection->map(function ($section) {
            return [
//                'section_name' => $section->section_name,
                'start_date' => $section->start_date,
                'end_date' => $section->end_date,
                'content' => $section->content->map(function ($content) {
                    return [
                        'name' => $content->descriptiveType->name,
                        'location' => $content->descriptiveType->location,
                        'type' => $content->descriptiveType->type,
                        'description' => $content->descriptiveType->description,
                    ];
                }),
            ];
        });
        $this->fee_taxes = $hotel->feeTaxes->map(function ($tax) {
            return [
                'name' => $tax->name,
                'net_value' => $tax->net_value,
                'rack_value' => $tax->rack_value,
                'tax' => $tax->tax,
                'type' => $tax->type,
            ];
        });
        $this->informational_service = $hotel->informativeServices->map(function ($service) {
            return [
                'name' => $service->service->name,
                'description' => $service->service->description,
                'cost' => $service->service->cost,
            ];
        });
        $this->promotions = $hotel->promotions->map(function ($promotion) {
            return [
                'promotion_name' => $promotion->promotion_name,
                'description' => $promotion->description,
                'validity_start' => $promotion->validity_start,
                'validity_end' => $promotion->validity_end,
                'booking_start' => $promotion->booking_start,
                'booking_end' => $promotion->booking_end,
                'terms_conditions' => $promotion->terms_conditions,
                'exclusions' => $promotion->exclusions,
                'deposit_info' => $promotion->deposit_info,
                'galleries' => $promotion->galleries->map(function ($gallery) {
                    return [
                        'images' => $gallery->images->map(function ($image) {
                            return [
                                'image_url' => $image->image_url,
                                'tag' => $image->tag,
                                'weight' => $image->weight,
                                'section' => $image->section->name,
                            ];
                        }),
                    ];
                }),
            ];
        });
        $this->rooms = $hotel->rooms->map(function ($room) {
            return [
                'name' => $room->name,
                'hbsi_data_mapped_name' => $room->hbsi_data_mapped_name,
                'description' => $room->description,
                'galleries' => $room->galleries->map(function ($gallery) {
                    return [
                        'gallery_name' => $gallery->gallery_name,
                        'description' => $gallery->description,
                        'images' => $gallery->images->map(function ($image) {
                            return [
                                'image_url' => $image->image_url,
                                'tag' => $image->tag,
                                'weight' => $image->weight,
                                'section' => $image->section->name,
                            ];
                        }),
                    ];
                }),
            ];
        });
        $this->key_mappings = $hotel->keyMappings->map(function ($mapping) {
            return [
                'key' => $mapping->key_id,
                'value' => $mapping->keyMappingOwner->name,
            ];
        });
        $this->galleries = $hotel->galleries->map(function ($gallery) {
            return [
                'gallery_name' => $gallery->gallery_name,
                'description' => $gallery->description,
                'images' => $gallery->images->map(function ($image) {
                    return [
                        'image_url' => $image->image_url,
                        'tag' => $image->tag,
                        'weight' => $image->weight,
                        'section' => $image->section->name,
                    ];
                }),
            ];
        });
    }
}
