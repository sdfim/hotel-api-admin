# Hotel Booking Supplier Integration Guide

This folder contains the interfaces and classes required for integrating new hotel booking suppliers. To add a new supplier, you must implement the following methods in your supplier class.

## Required Methods

### 1. `supplier(): SupplierNameEnum`
- **Description**: Returns the name of the supplier as a `SupplierNameEnum`.

### 2. `book(array $filters, ApiBookingInspector $bookingInspector): ?array`
- **Description**: Handles the booking process for the supplier.
- **Returns**: An array of `HotelBookResponseModel` or `null`.

### 3. `cancelBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata): ?array`
- **Description**: Cancels an existing booking.
- **Returns**: An array of `HotelBookResponseModel` or `null`.

### 4. `retrieveBooking(array $filters, ApiBookingsMetadata $apiBookingsMetadata, bool $isSync = false): ?array`
- **Description**: Retrieves booking details.
- **Returns**: An array of `HotelRetrieveBookingResponseModel` or `null`.

### 5. `listBookings(): ?array`
- **Description**: Lists all bookings for the supplier.
- **Returns**: An array or `null`.

### 6. `availabilityChange(array $filters, string $type = 'default'): ?array`
- **Description**: Checks availability changes for bookings.
- **Returns**: An array or `null`.

### 7. `priceCheck(array $filters): ?array`
- **Description**: Checks the price for a booking change.
- **Returns**: A structured array with price details or `null`.

### 8. `changeBooking(array $filters, string $type = 'soft'): ?array`
- **Description**: Changes an existing booking based on the provided filters.
- **Returns**: A structured array with the status and details of the change or `null`.

## Implementation Notes
- All methods must adhere to the contracts defined in `HotelBookingSupplierInterface`.
- Ensure proper validation and error handling for each method.
- Use the provided models (`ApiBookingInspector`, `ApiBookingsMetadata`, etc.) for consistency.

## Additional Information
- Refer to `BaseBookingSupplierInterface` for shared methods and properties.
- Use `HotelBookingSupplierRegistry` to register your supplier implementation.
- Ensure your supplier is properly bound in `HotelBookingServiceProvider`.

For further details, refer to the code comments in the respective interfaces and classes.
