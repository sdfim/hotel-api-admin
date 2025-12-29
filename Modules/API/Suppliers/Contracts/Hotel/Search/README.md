# Hotel Content and Pricing Supplier Interfaces Guide

This folder contains interfaces for hotel content, pricing, and supplier services. Below is a summary of the methods each interface requires.

## Interfaces and Methods

### `HotelContentSupplierInterface`
- **Purpose**: Defines methods for content suppliers.
- **Methods**:
    1. `search(array $filters): array` *(deprecated)*
        - **Description**: Deprecated method. For new implementations, return an empty array.
    2. `detail(Request $request): array|object` *(deprecated)*
        - **Description**: Deprecated method. For new implementations, return an empty array.

### `HotelContentV1SupplierInterface`
- **Purpose**: Defines methods for V1 content suppliers.
- **Methods**:
    1. `supplier(): SupplierNameEnum`
        - **Description**: Returns the supplier name as a `SupplierNameEnum`.
    2. `getResults(array $giataCodes): array`
        - **Description**: Retrieves results based on the provided GIATA codes.
    3. `getRoomsData(int $giataCode): array`
        - **Description**: Retrieves room data for a specific GIATA code.

### `HotelPricingSupplierInterface`
- **Purpose**: Defines methods for pricing suppliers.
- **Methods**:
    1. `price(array &$filters, array $searchInspector, array $preSearchData): ?array`
        - **Description**: Retrieves pricing information based on filters and pre-search data.
    2. `processPriceResponse(array $rawResponse, array $filters, string $searchId, array $pricingRules, array $pricingExclusionRules, array $giataIds): array`
        - **Description**: Processes the raw price response and applies rules to generate structured pricing data.

## Implementation Notes
- All methods must adhere to the contracts defined in their respective interfaces.
- Ensure proper validation and error handling for each method.
- Use the provided models and enums for consistency.

## Additional Information
- Refer to the code comments in the interfaces for further details.
- Follow the project standards for implementing and registering suppliers.
