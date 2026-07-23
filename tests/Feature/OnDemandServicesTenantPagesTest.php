<?php

use App\Livewire\Tenant\OnDemandServices\Detail;
use App\Livewire\Tenant\OnDemandServices\Index;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\OnDemandUnit;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

/**
 * @return array{0: Tenant}
 */
function createOnDemandServicesTenant(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'خدمات حسب الطلب',
        'handle' => 'ondemand-pages-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    return [$tenant];
}

test('tenant index lists published on-demand services with unit price', function () {
    [$tenant] = createOnDemandServicesTenant();

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('on-demand-services'),
        'title' => 'تركيب أرضيات',
        'slug' => 'flooring',
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
        'data' => [
            'subtitle' => 'حسب المتر',
            'price' => money_minor(20),
            'unit_type' => OnDemandUnit::SquareMeter,
            'unit_label' => '',
        ],
    ]);

    Livewire::test(Index::class)
        ->assertSuccessful()
        ->assertSee('تركيب أرضيات')
        ->assertSee('متر مربع');
});

test('tenant detail shows full on-demand service information', function () {
    [$tenant] = createOnDemandServicesTenant();

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('on-demand-services'),
        'title' => 'طباعة حسب الطلب',
        'slug' => 'custom-print',
        'status' => 'published',
        'published_at' => now(),
        'active' => true,
        'data' => [
            'subtitle' => 'طباعة احترافية',
            'body' => '<p>وصف الخدمة الكامل</p>',
            'price' => money_minor(15),
            'compare_price' => money_minor(20),
            'unit_type' => OnDemandUnit::Other,
            'unit_label' => 'متر طولي',
        ],
    ]);

    Livewire::test(Detail::class, ['slug' => 'custom-print'])
        ->assertSuccessful()
        ->assertSee('طباعة حسب الطلب')
        ->assertSee('طباعة احترافية')
        ->assertSee('وصف الخدمة الكامل')
        ->assertSee('متر طولي')
        ->assertSee('سيتم تفعيل طلب هذه الخدمة قريباً');
});
