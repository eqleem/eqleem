<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Contact extends Component
{
    public string $phone = '050 123 4567';

    public string $phoneDial = '+966501234567';

    public string $email = 'hello@ab3adalbait.sa';

    public string $whatsappUrl = 'https://wa.me/966501234567';

    /** @var array<int, array{name: string, icon: string, url: string}> */
    public array $socialLinks = [
        ['name' => 'Instagram', 'icon' => 'hugeicons:instagram', 'url' => 'https://instagram.com'],
        ['name' => 'X (Twitter)', 'icon' => 'hugeicons:new-twitter', 'url' => 'https://x.com'],
        ['name' => 'TikTok', 'icon' => 'hugeicons:tiktok', 'url' => 'https://tiktok.com'],
        ['name' => 'YouTube', 'icon' => 'hugeicons:youtube', 'url' => 'https://youtube.com'],
    ];

    public function render()
    {
        return tenantView('pages.contact')->title('اتصل بنا');
    }
}
