<?php

namespace Database\Seeders;

use App\Actions\SubscribeTenantToPlan;
use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use LucasDotVin\Soulbscription\Enums\PeriodicityType;
use LucasDotVin\Soulbscription\Models\Feature;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'free',
                'name' => 'free',
                'label' => 'مجانية',
                'price' => 0,
                'periodicity' => null,
                'periodicity_type' => null,
                'is_featured' => false,
                'meta' => [
                    'description' => 'ابدأ مجاناً واستكشف المنصة.',
                ],
            ],
            [
                'slug' => 'basic-monthly',
                'name' => 'basic-monthly',
                'label' => 'بيسك',
                'price' => 9900,
                'periodicity' => 1,
                'periodicity_type' => PeriodicityType::Month,
                'is_featured' => false,
                'meta' => [
                    'description' => 'مناسبة للصفحات الشخصية والمشاريع الصغيرة.',
                    'tier' => 'basic',
                    'interval' => 'monthly',
                ],
            ],
            [
                'slug' => 'basic-yearly',
                'name' => 'basic-yearly',
                'label' => 'بيسك',
                'price' => 99900,
                'periodicity' => 1,
                'periodicity_type' => PeriodicityType::Year,
                'is_featured' => true,
                'meta' => [
                    'description' => 'مناسبة للصفحات الشخصية والمشاريع الصغيرة.',
                    'tier' => 'basic',
                    'interval' => 'yearly',
                ],
            ],
            [
                'slug' => 'pro-monthly',
                'name' => 'pro-monthly',
                'label' => 'برو',
                'price' => 19900,
                'periodicity' => 1,
                'periodicity_type' => PeriodicityType::Month,
                'is_featured' => false,
                'meta' => [
                    'description' => 'للأعمال والصفحات الاحترافية.',
                    'tier' => 'pro',
                    'interval' => 'monthly',
                ],
            ],
            [
                'slug' => 'pro-yearly',
                'name' => 'pro-yearly',
                'label' => 'برو',
                'price' => 190000,
                'periodicity' => 1,
                'periodicity_type' => PeriodicityType::Year,
                'is_featured' => false,
                'meta' => [
                    'description' => 'للأعمال والصفحات الاحترافية.',
                    'tier' => 'pro',
                    'interval' => 'yearly',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['slug' => $plan['slug']],
                [
                    ...$plan,
                    'is_system' => true,
                    'active' => true,
                    'grace_days' => 0,
                ],
            );
        }

        $this->seedDomainFeature();

        Tenant::query()->each(function (Tenant $tenant): void {
            SubscribeTenantToPlan::make()->subscribeToFreePlan($tenant);
        });
    }

    protected function seedDomainFeature(): void
    {
        $domainFeature = Feature::query()->updateOrCreate(
            ['name' => 'domain'],
            [
                'consumable' => false,
                'quota' => false,
                'postpaid' => false,
                'periodicity' => null,
                'periodicity_type' => null,
            ],
        );

        $paidPlanSlugs = [
            'basic-monthly',
            'basic-yearly',
            'pro-monthly',
            'pro-yearly',
        ];

        Plan::query()
            ->whereIn('slug', $paidPlanSlugs)
            ->each(function (Plan $plan) use ($domainFeature): void {
                $plan->features()->syncWithoutDetaching([
                    $domainFeature->id => ['charges' => null],
                ]);
            });
    }
}
