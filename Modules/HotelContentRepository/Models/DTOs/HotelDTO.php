<?php

namespace Modules\HotelContentRepository\Models\DTOs;

class HotelDTO
{
    public $id;
    public $name;
    public $type;
    public $verified;
    public $address;
    public $star_rating;
    public $website;
    public $num_rooms;
    public $featured;
    public $location;
    public $content_source;
    public $room_images_source;
    public $property_images_source;
    public $channel_management;
    public $hotel_board_basis;
    public $default_currency;
    public $affiliations;
    public $attributes;
    public $descriptive_contents_section;
    public $fee_taxes;
    public $informative_services;
    public $promotions;
    public $rooms;
    public $key_mappings;
    public $travel_agency_commissions;
    public $galleries;
    public $contact_information;

    public function __construct($hotel)
    {
        $this->id = $hotel->id;
        $this->name = $hotel->name;
        $this->type = $hotel->type;
        $this->verified = (bool) $hotel->verified;
        $this->address = $hotel->address;
        $this->star_rating = $hotel->star_rating;
        $this->website = $hotel->website;
        $this->num_rooms = $hotel->num_rooms;
        $this->featured = (bool) $hotel->featured;
        $this->location = $hotel->location;
        $this->content_source = $hotel->contentSource->name;
        $this->room_images_source = $hotel->roomImagesSource->name;
        $this->property_images_source = $hotel->propertyImagesSource->name;
        $this->channel_management = (bool) $hotel->channel_management;
        $this->hotel_board_basis = $hotel->hotel_board_basis;
        $this->default_currency = $hotel->default_currency;
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
            return [
                'name' => $attribute->name,
                'attribute_value' => $attribute->attribute_value,
            ];
        });
        $this->descriptive_contents_section = $hotel->descriptiveContentsSection->map(function ($section) {
            return [
                'section_name' => $section->section_name,
                'start_date' => $section->start_date,
                'end_date' => $section->end_date,
                'content' => $section->content->map(function ($content) {
                    return [
                        'section_name' => $content->section_name,
                        'meta_description' => $content->meta_description,
                        'property_description' => $content->property_description,
                        'cancellation_policy' => $content->cancellation_policy,
                        'pet_policy' => $content->pet_policy,
                        'terms_conditions' => $content->terms_conditions,
                        'fees_paid_at_hotel' => $content->fees_paid_at_hotel,
                        'staff_contact_info' => $content->staff_contact_info,
                        'validity_start' => $content->validity_start,
                        'validity_end' => $content->validity_end,
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
        $this->informative_services = $hotel->informativeServices->map(function ($service) {
            return [
                'service_name' => $service->service_name,
                'service_description' => $service->service_description,
                'service_cost' => $service->service_cost,
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
