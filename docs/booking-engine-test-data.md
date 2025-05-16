# Seeder Structure

## OBE System

### Seeder: BookingEngineTestDataSeeder

---

### Properties
Creates hotel properties with matching names and GIATA IDs.

**Cancun**
- Nizuc Resort & Spa Test
- Garza Blanca Cancun Test
- Grand Velas Riviera Maya Test
- Banyan Tree Mayakoba Test

**St. Lucia**
- Sugar Beach Test

**Bahamas**
- The Cove Atlantis Test
- The Royal Atlantis Test
- The Reef Atlantis Test

> _Note: All property names have the "Test" suffix. The locale for each property matches its geolocation._

---

### Hotels

Hotels are created using `Hotel::updateOrCreate` based on the GIATA code.

Each hotel includes:
- `star_rating`, `num_rooms`, `featured_flag`, `sale_type`, and `travel_agent_commission`
- The `address` is copied from the property

---

### Hotel Rooms

Each hotel is seeded with **5 rooms**:
- `external_code`: matches hotel GIATA code
- `supplier_codes`: mocked Expedia code
- Includes fake description, bed groups, views, and size

---

### Vendor

A default `Vendor` is created (if not exists):
- Name: **"Booking Engine Vendor"**
- Verified: `true`

This vendor is used to associate with all created `Product` entries.

---

### Products & Deposits

Each hotel generates a corresponding `Product` of type `hotel`.

Deposit creation follows business rules:
- **2 hotels** get a `$100` fixed deposit
- **1 hotel** gets a `50%` percentage deposit
- Remaining hotels have no deposit

> _Deposit type is randomized among the hotel codes._

---

### Amenities

The following amenities are seeded using `ConfigAmenity`:

| Amenity Name                             | Type       | Hotel                 |
|------------------------------------------|------------|------------------------|
| Nizuc Resort & Spa - Virtuoso Amenities  | Virtuoso   | Nizuc Resort & Spa     |
| Nizuc Signature Amenities                | Signature  | Nizuc Resort & Spa     |
| The Cove Atlantis - Virtuoso             | Virtuoso   | The Cove Atlantis      |
| The Cove Atlantis - Signature            | Signature  | The Cove Atlantis      |

---

### Product Affiliations

Each product receives a `ProductAffiliation` that links it to Virtuoso/Signature consortia. Then:

- Amenities are attached as `ProductAffiliationAmenity` entries.
- **All Virtuoso amenities** are flagged as `is_paid: true`.
- **All Signature amenities** are `is_paid: false`.

> _Only products linked to hotels that match amenity naming will receive affiliation/amenity associations (i.e., Nizuc and The Cove)._

---

### Relationships Summary

| Entity                   | Linked To                         |
|--------------------------|------------------------------------|
| Property                 | Hotel (via `giata_code`)          |
| Mapping                  | Property (`giata_id`)             |
| HotelRoom                | Hotel (`hotel_id`)                |
| Product                  | Hotel (`related_id`, morph)       |
| ProductDepositInformation| Product (`product_id`)            |
| ProductAffiliation       | Product (`product_id`)            |
| ProductAffiliationAmenity| ProductAffiliation + Amenity      |

---

### Mappings

Uses the `Mapping` model to link each property to the supplier integration, based on the GIATA ID and supplier ID.

---

## How It Connects

The GIATA ID is the shared reference between:
- Admin’s Hotel (via `ExternalIdentifier`)
- OBE’s Property (via `Mapping`)

This enables *Travel Search* in the Admin system to resolve and display supplier data correctly from the OBE system.

---

### Hotel Mapping Table

| Hotel Name                  | GIATA ID | City      | OBE Supplier ID |
|------------------------------|----------|-----------|-----------------|
| Nizuc Resort & Spa Test      | 1004     | Cancún    | 51721           |
| Garza Blanca Cancun Test     | 1005     | Cancún    | 51721           |
| Grand Velas Riviera Maya Test| 1006     | Cancún    | 51721           |
| Banyan Tree Mayakoba Test    | 1007     | Cancún    | 51721           |
| Sugar Beach Test             | 1008     | St. Lucia | 51722           |
| The Cove Atlantis Test       | 1009     | Bahamas   | 48187           |
| The Royal Atlantis Test      | 1010     | Bahamas   | 48187           |
| The Reef Atlantis Test       | 1011     | Bahamas   | 48187           |

---

## Important

> These seeders are not intended for production environments.  
> They should only be executed in development or staging environments.

---

## How to Run

```bash
php artisan db:seed --class=BookingEngineTestDataSeeder
```

## How it Works

After running the seeders, both systems are ready for integration test simulations.

The creation of hotels, destinations and regions made here, and the mapping with giatta serve for the successful connection with the API. Follow more instructions in the `docs/travel-connect-test-data-explanation.md` documentation in the [admin repository](https://github.com/ultimatejetvacations/ujv-dev.travelagentadmin.com).
