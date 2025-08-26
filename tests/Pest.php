<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

use Database\Seeders\ConfigServiceTypeSeeder;
use Database\Seeders\GeneralConfigurationSeeder;
use Database\Seeders\SuppliersSeeder;
use Illuminate\Support\Facades\Artisan;
use Modules\Insurance\Seeders\InsuranceRateTierSeeder;
use Modules\Insurance\Seeders\InsuranceRestrictionTypeSeeder;
use Modules\Insurance\Seeders\InsuranceTypeSeeder;
use Modules\Insurance\Seeders\InsuranceVendorSeeder;
use Modules\Insurance\Seeders\TripMateDefaultRestrictionsSeeder;
use Tests\RefreshDatabaseMany;
use Tests\TestCase;
use App\Models\User; // Import User model

// Your existing global TestCase usage
uses(TestCase::class)->in('Feature');

// Your existing API/HotelContentRepository setup
uses(RefreshDatabaseMany::class)
    ->beforeEach(function () {
        if (! isset($this->user)) {
            Artisan::call('db:seed', ['--class' => SuppliersSeeder::class]);
            if (! \App\Models\GeneralConfiguration::exists()) {
                Artisan::call('db:seed', ['--class' => GeneralConfigurationSeeder::class]);
            }

            $this->user = \App\Models\User::factory()->create();
            $token = $this->user->createToken('Test');
            $this->accessToken = $token->plainTextToken;
            \App\Models\Channel::create([
                'token_id' => $token->accessToken->id,
                'access_token' => $token->plainTextToken,
                'name' => 'Test channel',
                'description' => 'Temp channel',
            ]);
        }

        // Apply authentication for every request in the test
        $this->actingAs($this->user)->withHeader(
            'Authorization',
            'Bearer ' . $this->accessToken
        );
    })
    ->in('Feature/API/HotelContentRepository');

/*
|--------------------------------------------------------------------------
| Custom Authorized Actions Setup
|--------------------------------------------------------------------------
|
| This section sets up the required traits and authentication for tests
| within the 'Feature/CustomAuthorizedActions' directory.
|
*/
uses() // Ensure TestCase is used here to access auth()
    ->beforeEach(function () {
        // Call the auth() method defined in your base TestCase
        // 'test()' provides access to the current test case instance.
        // This relies on your Tests\TestCase having the auth() method.
        if (method_exists(test(), 'auth')) {
            test()->auth();
        } else {
            // Fallback for direct creation if auth() isn't available on test() for some reason
            // This generally shouldn't be needed if TestCase is properly extended.
            $user = User::factory()->create();
            $user->roles()->create(['name' => 'admin', 'slug' => 'admin']);

            test()->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
            ]);
        }
    })
    ->in('Feature/CustomAuthorizedActions');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of powerful expectations that you can use
| to check those conditions.
|
| When you pass a value to the "expect()" function, you will receive an object that has a
| number of methods that can be used to make assertions about that value.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the amount of code in your tests files.
|
*/

function something()
{
    // ..
}
