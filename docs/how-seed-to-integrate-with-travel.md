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

The creation of hotels, destinations and regions made here, and the mapping with giatta serve for the successful connection with the API. Follow more instructions in the `docs/how-to-integrate-travel-connect` documentation in the [admin repository](https://github.com/ultimatejetvacations/ujv-dev.travelagentadmin.com).
