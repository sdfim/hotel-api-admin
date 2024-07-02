<?php

namespace Modules\API\Resources\Booking\Hotel;

/**
 * @OA\Schema(
 *   schema="BookingAddPassengersRequest",
 *   title="Booking Add Passengers Request",
 *   description="Schema Booking Add Passengers Request",
 *   type="object",
 *   required={"passengers"},
 *
 *   @OA\Property(
 *     property="passengers",
 *     type="array",
 *     description="Passengers",
 *
 *     @OA\Items(
 *       type="object",
 *       required={"title", "given_name", "family_name", "date_of_birth", "booking_items"},
 *
 *       @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title",
 *         example="mr"
 *       ),
 *       @OA\Property(
 *         property="given_name",
 *         type="string",
 *         description="Given Name",
 *         example="John"
 *       ),
 *       @OA\Property(
 *         property="family_name",
 *         type="string",
 *         description="Family Name",
 *         example="Portman"
 *       ),
 *       @OA\Property(
 *         property="date_of_birth",
 *         type="string",
 *         description="Date of Birth",
 *         example="2080-12-14"
 *       ),
 *       @OA\Property(
 *         property="booking_items",
 *         type="array",
 *         description="Booking Items",
 *
 *         @OA\Items(
 *           type="object",
 *           required={"booking_item", "room"},
 *
 *           @OA\Property(
 *             property="booking_item",
 *             type="string",
 *             description="Booking Item",
 *             example="bed7af51-836a-4d7e-8f82-8efe8b1825d4"
 *           ),
 *           @OA\Property(
 *             property="room",
 *             type="string",
 *             description="Room",
 *             example="1"
 *           )
 *         )
 *       )
 *     )
 *   ),
 *   @OA\Property(
 *     property="booking_id",
 *     type="string",
 *     description="Booking ID",
 *     example="5abcab52-00b0-423c-aafe-2fed6f6d1f4e"
 *   )
 * ),
 *
 * @OA\Examples(
 *     example="BookingAddPassengersRequest",
 *     summary="Example Booking Add Passengers Request",
 *     value=
 *     {
 *      "passengers": {
 *        {
 *          "title": "mr",
 *          "given_name": "Adult1",
 *          "family_name": "Portman",
 *          "date_of_birth": "1988-12-14",
 *          "booking_items": {
 *            {
 *              "booking_item": "387050aa-d06d-4587-aa67-2a32cfe2fc9e",
 *              "room": "1"
 *            },
 *            {
 *              "booking_item": "ec8436ac-93e8-47f6-8782-925c812c8e9f",
 *              "room": "1"
 *            }
 *          }
 *        },
 *        {
 *          "title": "mr",
 *          "given_name": "Adult2",
 *          "family_name": "Portman",
 *          "date_of_birth": "1988-12-14",
 *          "booking_items": {
 *            {
 *              "booking_item": "387050aa-d06d-4587-aa67-2a32cfe2fc9e",
 *              "room": "1"
 *            },
 *            {
 *              "booking_item": "ec8436ac-93e8-47f6-8782-925c812c8e9f",
 *              "room": "1"
 *            }
 *          }
 *        },
 *        {
 *          "title": "mr",
 *          "given_name": "Adult3",
 *          "family_name": "Portman",
 *          "date_of_birth": "1988-12-14",
 *          "booking_items": {
 *            {
 *              "booking_item": "387050aa-d06d-4587-aa67-2a32cfe2fc9e",
 *              "room": "2"
 *            }
 *          }
 *        },
 *        {
 *          "title": "mr",
 *          "given_name": "Adult4",
 *          "family_name": "Portman",
 *          "date_of_birth": "1988-12-14",
 *          "booking_items": {
 *            {
 *              "booking_item": "387050aa-d06d-4587-aa67-2a32cfe2fc9e",
 *              "room": "2"
 *            }
 *          }
 *        },
 *
 *        {
 *          "title": "ms",
 *          "given_name": "Children1",
 *          "family_name": "Donald",
 *          "date_of_birth": "2010-01-18",
 *          "booking_items": {
 *            {
 *              "booking_item": "ec8436ac-93e8-47f6-8782-925c812c8e9f",
 *              "room": "1"
 *            }
 *          }
 *        },
 *        {
 *          "title": "ms",
 *          "given_name": "Children2",
 *          "family_name": "Donald",
 *          "date_of_birth": "2009-09-18",
 *          "booking_items": {
 *            {
 *              "booking_item": "ec8436ac-93e8-47f6-8782-925c812c8e9f",
 *              "room": "1"
 *            }
 *          }
 *        }
 *      }
 *    }
 * ),
 */
class BookingAddPassengersRequest
{
}
