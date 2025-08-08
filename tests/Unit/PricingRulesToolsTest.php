<?php

use App\Models\Channel;
use App\Models\PricingRule;
use App\Models\PricingRuleCondition;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\API\Tools\PricingRulesTools;

uses(Tests\TestCase::class);

beforeEach(function () {
    // Mock current date for consistent tests
    $this->now = Carbon::create(2025, 7, 4);
    Carbon::setTestNow($this->now);

    // Create a mock Channel without actually saving to database
    $this->channel = $this->createMock(Channel::class);
    $this->channel->method('__get')->willReturnMap([
        ['id', 1],
        ['token_id', 1],
        ['name', 'Test Channel'],
        ['description', 'Test Description'],
        ['access_token', 'test-access-token'],
    ]);

    $this->channelId = 1;

    $user = User::factory()->create();
    $this->actingAs($user);

    PersonalAccessToken::forceCreate([
        'id' => 1,
        'name' => 'test-token',
        'token' => hash('sha256', 'test-access-token'),
        'tokenable_type' => User::class,
        'tokenable_id' => $user->id,
    ]);

    Channel::factory()->create([
        'id' => $this->channelId,
        'token_id' => 1,
        'name' => 'Test Channel',
        'access_token' => 'test-access-token',
    ]);

    // Mock the request to have a bearer token
    $request = new Request;
    $request->headers->set('Authorization', 'Bearer test-access-token');
    $this->app->instance('request', $request);

    // Base query parameters that would be used in most tests
    $this->baseQuery = [
        'type' => 'hotel',
        'checkin' => '2025-07-15',
        'checkout' => '2025-07-20',
        'occupancy' => [
            [
                'adults' => 2,
                'children_ages' => [5],
            ],
            [
                'adults' => 3,
            ],
        ],
        'giata_ids' => [45422295, 38711975],
        'rating' => 4.0,
    ];

    $this->supplierRequestGiataIds = [45422295, 38711975];

    // Initialize the class to test
    $this->pricingRulesTools = new PricingRulesTools;
});

test('rules returns empty array for standalone package', function () {
    // Test the standalone package exclusion
    $query = array_merge($this->baseQuery, [
        'query_package' => 'standalone',
        'supplier' => 'Expedia',
    ]);

    $result = $this->pricingRulesTools->rules($query, $this->supplierRequestGiataIds);

    expect($result)->toBeEmpty();
});

test('rules handles channel conditions', function () {
    // Create a pricing rule that matches our channel with equals
    $matchingRule = PricingRule::create([
        'name' => 'Channel Test Rule - Match Equals',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'channel_id',
        'compare' => '=',
        'value_from' => $this->channelId,
    ]);

    // Create a rule that matches with not_equal (should match because we're not equal to a different channel)
    $notEqualRule = PricingRule::create([
        'name' => 'Channel Test Rule - Not Equal',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $notEqualRule->id,
        'field' => 'channel_id',
        'compare' => '!=',
        'value_from' => $this->channelId + 1, // Different channel ID, so our channel != this value
    ]);

    // Create a rule that should not match due to different channel ID
    $nonMatchingRule = PricingRule::create([
        'name' => 'Channel Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'channel_id',
        'compare' => '=',
        'value_from' => $this->channelId + 1, // Different channel ID
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    // Should return 2 matching rules (equals and not equal), ordered by weight desc
    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toEqual($matchingRule->id);
    expect($result[1]['id'])->toEqual($notEqualRule->id);
});

test('rules handles property conditions', function () {
    $giataId = $this->supplierRequestGiataIds[0];
    // 45422295
    $secondGiataId = $this->supplierRequestGiataIds[1];

    // 38711975
    // Create rule that matches the property with equals
    $matchingRule = PricingRule::create([
        'name' => 'Property Test Rule - Match Equals',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'property',
        'compare' => '=',
        'value_from' => $giataId,
    ]);

    // Create rule that matches with not equal (using different property)
    $notEqualRule = PricingRule::create([
        'name' => 'Property Test Rule - Not Equal',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $notEqualRule->id,
        'field' => 'property',
        'compare' => '!=',
        'value_from' => 99999, // Not in our supplier request GIATA IDs
    ]);

    // Create rule that matches with less than
    $lessThanRule = PricingRule::create([
        'name' => 'Property Test Rule - Less Than',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 3,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $lessThanRule->id,
        'field' => 'property',
        'compare' => '<',
        'value_from' => 50000000, // Higher than our GIATA IDs
    ]);

    // Create rule that doesn't match any of our properties
    $nonMatchingRule = PricingRule::create([
        'name' => 'Property Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'property',
        'compare' => '=',
        'value_from' => 99999, // Not in our supplier request GIATA IDs
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    // Should return 3 matching rules, ordered by weight desc
    expect($result)->toHaveCount(3);
    expect($result[0]['id'])->toEqual($matchingRule->id);
    expect($result[1]['id'])->toEqual($notEqualRule->id);
    expect($result[2]['id'])->toEqual($lessThanRule->id);
});

test('rules handles destination conditions', function () {
    $query = array_merge($this->baseQuery, [
        'destination' => '961',
    ]);

    // Create rule that matches the destination with equals
    $matchingRule = PricingRule::create([
        'name' => 'Destination Test Rule - Match Equals',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'destination',
        'compare' => '=',
        'value_from' => '961',
    ]);

    // Create rule that matches with not equal
    $notEqualRule = PricingRule::create([
        'name' => 'Destination Test Rule - Not Equal',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $notEqualRule->id,
        'field' => 'destination',
        'compare' => '!=',
        'value_from' => 'Miami',
    ]);

    // Create rule that matches with greater than (numeric destination)
    $greaterThanRule = PricingRule::create([
        'name' => 'Destination Test Rule - Greater Than',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 3,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $greaterThanRule->id,
        'field' => 'destination',
        'compare' => '>',
        'value_from' => '500', // Less than our destination 961
    ]);

    // Create rule that doesn't match our destination
    $nonMatchingRule = PricingRule::create([
        'name' => 'Destination Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'destination',
        'compare' => '=',
        'value_from' => 'Paris',
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($query, $this->supplierRequestGiataIds);

    // Should return 3 matching rules, ordered by weight desc
    expect($result)->toHaveCount(3);
    expect($result[0]['id'])->toEqual($matchingRule->id);
    expect($result[1]['id'])->toEqual($notEqualRule->id);
    expect($result[2]['id'])->toEqual($greaterThanRule->id);
});

test('rules handles travel date conditions', function () {
    $checkIn = '2025-07-15';

    // Create rule that matches the travel date with equals
    $matchingRule = PricingRule::create([
        'name' => 'Travel Date Test Rule - Match Equals',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'travel_date',
        'compare' => '=',
        'value_from' => $checkIn,
    ]);

    // Create rule that matches with greater than or equal
    $gteRule = PricingRule::create([
        'name' => 'Travel Date Test Rule - GTE',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $gteRule->id,
        'field' => 'travel_date',
        'compare' => '>=',
        'value_from' => $checkIn,
    ]);

    // Create rule that matches with less than
    $ltRule = PricingRule::create([
        'name' => 'Travel Date Test Rule - Less Than',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 3,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $ltRule->id,
        'field' => 'travel_date',
        'compare' => '<',
        'value_from' => '2025-08-01', // After our travel date
    ]);

    // Create rule with travel date that doesn't match
    $nonMatchingRule = PricingRule::create([
        'name' => 'Travel Date Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'travel_date',
        'compare' => '=',
        'value_from' => '2025-08-01',
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    // Should return 3 matching rules, ordered by weight desc
    expect($result)->toHaveCount(4);
    expect($result[0]['id'])->toEqual($matchingRule->id);
    expect($result[1]['id'])->toEqual($gteRule->id);
    expect($result[2]['id'])->toEqual($ltRule->id);
});

test('rules handles date of stay conditions', function () {
    $checkIn = '2025-07-15';
    $checkOut = '2025-07-20';

    // Create rule that matches the date of stay (between check-in and check-out)
    $betweenRule = PricingRule::create([
        'name' => 'Date of Stay Test Rule - Between',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $betweenRule->id,
        'field' => 'date_of_stay',
        'compare' => 'between',
        'value_from' => $checkIn,
        'value_to' => $checkOut,
    ]);

    // Create rule that matches with equals (specific date within stay)
    $equalsRule = PricingRule::create([
        'name' => 'Date of Stay Test Rule - Equals',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $equalsRule->id,
        'field' => 'date_of_stay',
        'compare' => '=',
        'value_from' => '2025-07-15', // Date within our stay period
    ]);

    // Create rule that matches with greater than or equal
    $gteRule = PricingRule::create([
        'name' => 'Date of Stay Test Rule - GTE',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 3,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $gteRule->id,
        'field' => 'date_of_stay',
        'compare' => '>=',
        'value_from' => '2025-07-10', // Earlier than our check-in
    ]);

    // Create rule with date of stay that doesn't overlap
    $nonMatchingRule = PricingRule::create([
        'name' => 'Date of Stay Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'date_of_stay',
        'compare' => 'between',
        'value_from' => '2025-08-01',
        'value_to' => '2025-08-10',
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    expect($result)->toHaveCount(3);
    expect($result[0]['id'])->toEqual($betweenRule->id);
    expect($result[1]['id'])->toEqual($equalsRule->id);
    expect($result[2]['id'])->toEqual($gteRule->id);
});

test('rules handles booking date conditions', function () {
    // Current date is 2025-07-04 (from setUp)
    // Create rule that matches with greater than or equal
    $gteRule = PricingRule::create([
        'name' => 'Booking Date Test Rule - GTE',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $gteRule->id,
        'field' => 'booking_date',
        'compare' => '>=',
        'value_from' => $this->now->copy()->subDay()->format('Y-m-d'), // Yesterday
    ]);

    // Create rule that matches with less than
    $ltRule = PricingRule::create([
        'name' => 'Booking Date Test Rule - Less Than',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 3,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $ltRule->id,
        'field' => 'booking_date',
        'compare' => '<',
        'value_from' => $this->now->copy()->addDay()->format('Y-m-d'), // Tomorrow
    ]);

    // Create rule with booking date that doesn't match
    $nonMatchingRule = PricingRule::create([
        'name' => 'Booking Date Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'booking_date',
        'compare' => '=',
        'value_from' => $this->now->copy()->addWeek()->format('Y-m-d'), // Next week
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    // Should return 3 matching rules, ordered by weight desc
    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toEqual($gteRule->id);
    expect($result[1]['id'])->toEqual($ltRule->id);
});

test('rules handles total guests conditions', function () {
    // Total guests is 3 from our mocked GeneralTools
    $query = array_merge($this->baseQuery, [
        'occupancy' => [
            [
                'adults' => 2,
                'children_ages' => [5],
            ],
        ],
    ]);

    // Create rule that matches the total guests with equals
    $equalsRule = PricingRule::create([
        'name' => 'Total Guests Test Rule - Equals',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $equalsRule->id,
        'field' => 'total_guests',
        'compare' => '=',
        'value_from' => 3,
    ]);

    // Create rule that matches with less than or equal
    $lteRule = PricingRule::create([
        'name' => 'Total Guests Test Rule - LTE',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $lteRule->id,
        'field' => 'total_guests',
        'compare' => '<',
        'value_from' => 5, // Our guests (3) <= 5
    ]);

    // Create rule that doesn't match our total guests
    $nonMatchingRule = PricingRule::create([
        'name' => 'Total Guests Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'total_guests',
        'compare' => '=',
        'value_from' => 4,
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($query, $this->supplierRequestGiataIds);

    // Should return 3 matching rules, ordered by weight desc
    expect($result)->toHaveCount(2);
    expect($result[0]['id'])->toEqual($equalsRule->id);
    expect($result[1]['id'])->toEqual($lteRule->id);
});

test('rules handles days until departure conditions', function () {
    // Current date is 2025-07-04, check-in date is 2025-07-15, so days_until_departure = 11
    // Create rule that matches the days until departure with equals
    $equalsRule = PricingRule::create([
        'name' => 'Days Until Departure Test Rule - Equals',
        'weight' => 15,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $equalsRule->id,
        'field' => 'days_until_departure',
        'compare' => '=',
        'value_from' => 11,
    ]);

    // Create rule that matches with greater than or equal
    $gteRule = PricingRule::create([
        'name' => 'Days Until Departure Test Rule - GTE',
        'weight' => 12,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 7,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $gteRule->id,
        'field' => 'days_until_departure',
        'compare' => '>=',
        'value_from' => 10, // Our days (11) >= 10
    ]);

    // Create rule that matches with less than
    $ltRule = PricingRule::create([
        'name' => 'Days Until Departure Test Rule - Less Than',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 3,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $ltRule->id,
        'field' => 'days_until_departure',
        'compare' => '<',
        'value_from' => 15, // Our days (11) < 15
    ]);

    // Create rule that doesn't match our days until departure
    $nonMatchingRule = PricingRule::create([
        'name' => 'Days Until Departure Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'days_until_departure',
        'compare' => '=',
        'value_from' => 20,
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    // Should return 3 matching rules, ordered by weight desc
    expect($result)->toHaveCount(3);
    expect($result[0]['id'])->toEqual($equalsRule->id);
    expect($result[1]['id'])->toEqual($gteRule->id);
    expect($result[2]['id'])->toEqual($ltRule->id);
});

test('rules handles nights conditions', function () {
    // Check-in: 2025-07-15, Check-out: 2025-07-20, so nights = 5
    // Create rule that matches the nights
    $matchingRule = PricingRule::create([
        'name' => 'Nights Test Rule - Match',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'nights',
        'compare' => '=',
        'value_from' => 5,
    ]);

    // Create rule that doesn't match our nights
    $nonMatchingRule = PricingRule::create([
        'name' => 'Nights Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'nights',
        'compare' => '=',
        'value_from' => 7,
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toEqual($matchingRule->id);
});

test('rules handles rating conditions', function () {
    // Rating is 4.0 from our baseQuery
    // Create rule that matches the rating
    $matchingRule = PricingRule::create([
        'name' => 'Rating Test Rule - Match',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'rating',
        'compare' => '=',
        'value_from' => 4.0,
    ]);

    // Create rule that doesn't match our rating
    $nonMatchingRule = PricingRule::create([
        'name' => 'Rating Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'rating',
        'compare' => '=',
        'value_from' => 5.0,
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toEqual($matchingRule->id);
});

test('rules handles number of rooms conditions', function () {
    // Number of rooms is 2 from our baseQuery occupancy array
    // Create rule that matches the number of rooms
    $matchingRule = PricingRule::create([
        'name' => 'Number of Rooms Test Rule - Match',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'number_of_rooms',
        'compare' => '=',
        'value_from' => 2,
    ]);

    // Create rule that doesn't match our number of rooms
    $nonMatchingRule = PricingRule::create([
        'name' => 'Number of Rooms Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'number_of_rooms',
        'compare' => '=',
        'value_from' => 1,
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toEqual($matchingRule->id);
});

test('rules handles multiple conditions', function () {
    // Create rule with multiple matching conditions
    $matchingRule = PricingRule::create([
        'name' => 'Multi-Condition Test Rule - Match',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    // Add multiple conditions to the same rule
    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'channel_id',
        'compare' => '=',
        'value_from' => $this->channelId,
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'destination',
        'compare' => '=',
        'value_from' => 'Cancun',
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $matchingRule->id,
        'field' => 'rating',
        'compare' => '=',
        'value_from' => 4.0,
    ]);

    // Create another rule that won't match due to one condition being off
    $nonMatchingRule = PricingRule::create([
        'name' => 'Multi-Condition Test Rule - No Match',
        'weight' => 5,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    // Add matching conditions
    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'channel_id',
        'compare' => '=',
        'value_from' => $this->channelId,
    ]);

    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'destination',
        'compare' => '=',
        'value_from' => 'Cancun',
    ]);

    // Add one non-matching condition
    PricingRuleCondition::create([
        'pricing_rule_id' => $nonMatchingRule->id,
        'field' => 'rating',
        'compare' => '=',
        'value_from' => 5.0, // Different from our 4.0 rating
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    expect($result)->toHaveCount(1);
    expect($result[0]['id'])->toEqual($matchingRule->id);
});

test('rules ordered by weight', function () {
    // Create several rules with different weights
    $rule1 = PricingRule::create([
        'name' => 'Weight Rule 1',
        'weight' => 30,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    $rule2 = PricingRule::create([
        'name' => 'Weight Rule 2',
        'weight' => 20,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    $rule3 = PricingRule::create([
        'name' => 'Weight Rule 3',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 15,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
    ]);

    // Test the rules method
    $result = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);

    // Should be ordered by weight descending
    expect($result[0]['id'])->toEqual($rule1->id);
    expect($result[1]['id'])->toEqual($rule2->id);
    expect($result[2]['id'])->toEqual($rule3->id);
});

test('rules with exclude parameter', function () {
    // Create a normal pricing rule
    $normalRule = PricingRule::create([
        'name' => 'Normal Rule',
        'weight' => 10,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 5,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
        'is_exclude_action' => false,
    ]);

    // Create an exclude rule
    $excludeRule = PricingRule::create([
        'name' => 'Exclude Rule',
        'weight' => 20,
        'rule_start_date' => $this->now->copy()->subDay(),
        'rule_expiration_date' => $this->now->copy()->addMonth(),
        'manipulable_price_type' => 'net_price',
        'price_value' => 10,
        'price_value_type' => 'percentage',
        'price_value_target' => 'per_night',
        'is_exclude_action' => true,
    ]);

    // Test normal rules (is_exclude = false)
    $normalResults = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds);
    expect($normalResults)->toHaveCount(1);
    expect($normalResults[0]['id'])->toEqual($normalRule->id);

    // Test exclude rules (is_exclude = true)
    $excludeResults = $this->pricingRulesTools->rules($this->baseQuery, $this->supplierRequestGiataIds, true);
    expect($excludeResults)->toHaveCount(1);
    expect($excludeResults[0]['id'])->toEqual($excludeRule->id);
});
