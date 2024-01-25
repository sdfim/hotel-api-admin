<?php

namespace Modules\Enums;

enum RouteEnum: string
{
    case ROUTE_ADD_ITEM = 'addItem';
    case REMOVE_ITEM = 'removeItem';
    case CHANGE_ITEMS = 'changeItems';
    case RETRIEVE_ITEMS = 'retrieveItems';
    case ADD_PASSENGERS = 'addPassengers';
    case BOOK = 'book';
    case LIST_BOOKINGS = 'listBookings';
    case RETRIEVE_BOOKING = 'retrieveBooking';
    case CANCEL_BOOKING = 'cancelBooking';
    case CHANGE_BOOKING = 'changeBooking';
}
