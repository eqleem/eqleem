<?php

use App\Models\Plan;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\FeatureConsumption;
use LucasDotVin\Soulbscription\Models\FeaturePlan;
use LucasDotVin\Soulbscription\Models\FeatureTicket;
use LucasDotVin\Soulbscription\Models\Subscription;
use LucasDotVin\Soulbscription\Models\SubscriptionRenewal;

return [
    'database' => [
        'cancel_migrations_autoloading' => true,
    ],

    'feature_tickets' => env('SOULBSCRIPTION_FEATURE_TICKETS', false),

    'models' => [

        'feature' => Feature::class,

        'feature_consumption' => FeatureConsumption::class,

        'feature_ticket' => FeatureTicket::class,

        'feature_plan' => FeaturePlan::class,

        'plan' => Plan::class,

        'subscriber' => [
            'uses_uuid' => env('SOULBSCRIPTION_SUBSCRIBER_USES_UUID', false),
        ],

        'subscription' => Subscription::class,

        'subscription_renewal' => SubscriptionRenewal::class,
    ],
];
