<?php

namespace Modules\API\Suppliers\Enums\HBSI;

enum HbsiFeeTaxTypeEnum: string
{
    case BED_TAX = 'Bed tax';
    case CITY_HOTEL_FEE = 'City hotel fee';
    case CITY_TAX = 'City tax';
    case COUNTY_TAX = 'County tax';
    case ENERGY_TAX = 'Energy tax';
    case FEDERAL_TAX = 'Federal tax';
    case FOOD_BEVERAGE_TAX = 'Food & beverage tax';
    case LODGING_TAX = 'Lodging tax';
    case MAINTENANCE_FEE = 'Maintenance fee';
    case OCCUPANCY_TAX = 'Occupancy tax';
    case PACKAGE_FEE = 'Package fee';
    case RESORT_FEE = 'Resort Fee';
    case SALES_TAX = 'Sales tax';
    case SERVICE_CHARGE = 'Service charge';
    case STATE_TAX = 'State tax';
    case SURCHARGE = 'Surcharge';
    case TOTAL_TAX = 'Total tax';
    case TOURISM_TAX = 'Tourism tax';
    case VAT_GST_TAX = 'VAT/GST tax';
    case SURPLUS_LINES_TAX = 'Surplus Lines Tax';
    case INSURANCE_PREMIUM_TAX = 'Insurance Premium Tax';
    case APPLICATION_FEE = 'Application Fee';
    case EXPRESS_HANDLING_FEE = 'Express Handling Fee';
    case EXEMPT = 'Exempt';
    case STANDARD = 'Standard';
    case ZERO_RATED = 'Zero-rated';
    case MISCELLANEOUS = 'Miscellaneous';
    case ROOM_TAX = 'Room Tax';
    case EARLY_CHECKOUT_FEE = 'Early checkout fee';
    case COUNTRY_TAX = 'Country tax';
    case EXTRA_PERSON_CHARGE = 'Extra person charge';
    case BANQUET_SERVICE_FEE = 'Banquet service fee';
    case ROOM_SERVICE_FEE = 'Room service fee';
    case LOCAL_FEE = 'Local fee';
    case GOODS_SERVICES_TAX = 'Goods and services tax (GST)';
    case VALUE_ADDED_TAX = 'Value Added Tax (VAT)';
    case CRIB_FEE = 'Crib fee';
    case ROLLAWAY_FEE = 'Rollaway fee';

    // New entries with "Levy"
    case BED_LEVY = 'Bed Levy';
    case CITY_HOTEL_LEVY = 'City hotel Levy';
    case CITY_LEVY = 'City Levy';
    case COUNTY_LEVY = 'County Levy';
    case ENERGY_LEVY = 'Energy Levy';
    case FEDERAL_LEVY = 'Federal Levy';
    case FOOD_BEVERAGE_LEVY = 'Food & beverage Levy';
    case LODGING_LEVY = 'Lodging Levy';
    case MAINTENANCE_LEVY = 'Maintenance Levy';
    case OCCUPANCY_LEVY = 'Occupancy Levy';
    case PACKAGE_LEVY = 'Package Levy';
    case RESORT_LEVY = 'Resort Levy';
    case SALES_LEVY = 'Sales Levy';
    case STATE_LEVY = 'State Levy';
    case TOTAL_LEVY = 'Total Levy';
    case TOURISM_LEVY = 'Tourism Levy';
    case VAT_GST_LEVY = 'VAT/GST Levy';
    case SURPLUS_LINES_LEVY = 'Surplus Lines Levy';
    case INSURANCE_PREMIUM_LEVY = 'Insurance Premium Levy';
    case APPLICATION_LEVY = 'Application Levy';
    case EXPRESS_HANDLING_LEVY = 'Express Handling Levy';
    case ROOM_LEVY = 'Room Levy';
    case COUNTRY_LEVY = 'Country Levy';
    case BANQUET_SERVICE_LEVY = 'Banquet service Levy';
    case ROOM_SERVICE_LEVY = 'Room service Levy';
    case LOCAL_LEVY = 'Local Levy';
    case GOODS_SERVICES_LEVY = 'Goods and services Levy (GST)';
    case VALUE_ADDED_LEVY = 'Value Added Levy (VAT)';
    case CRIB_LEVY = 'Crib Levy';
    case ROLLAWAY_LEVY = 'Rollaway Levy';
    case PROMOTION_LEVY = 'Promotion Levy';
}
