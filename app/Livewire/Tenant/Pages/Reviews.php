<?php

namespace App\Livewire\Tenant\Pages;

use App\Models\Client;
use App\Models\Review;
use App\Models\Setting;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Reviews extends Component
{
    use WithPagination;

    public ?int $contentId = null;

    public ?int $editingReviewId = null;

    public string $title = '';

    public string $score = '';

    public int $rating = 5;

    public function openAddReview(): void
    {
        if (! authClient() instanceof Client) {
            rememberClientAuthIntended(route('tenant.pages.reviews', [
                'tenant' => tenant('handle'),
            ]));

            $this->dispatch('open-modal', name: 'reviews-login-modal');

            return;
        }

        $this->loadReviewFormForClient();
        $this->dispatch('open-modal', name: 'add-testimonial-modal');
    }

    public function setRating(int $rating): void
    {
        $this->rating = max(1, min(5, $rating));
    }

    public function submitReview(): void
    {
        $client = authClient();

        if (! $client instanceof Client) {
            $this->openAddReview();

            return;
        }

        $validated = $this->validate([
            'title' => ['required', 'string', 'min:2', 'max:255'],
            'score' => ['required', 'string', 'min:5', 'max:5000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ], [], [
            'title' => 'عنوان التقييم',
            'score' => 'نص التقييم',
            'rating' => 'التقييم',
        ]);

        $tenantId = currentTenantId();

        abort_unless($tenantId, 404);

        $existing = $this->clientReviewFor($client);

        $payload = [
            'tenant_id' => $tenantId,
            'client_id' => $client->id,
            'content_id' => $this->contentId,
            'title' => $validated['title'],
            'score' => $validated['score'],
            'rating' => $validated['rating'],
            'name' => $client->displayName(),
            'email' => $client->profileForTenant()['email'] ?? $client->email,
            'phone' => $client->profileForTenant()['phone'] ?? $client->phone,
            'published' => true,
        ];

        if ($existing instanceof Review) {
            $existing->update($payload);
            $message = 'تم تحديث تقييمك بنجاح.';
        } else {
            $existing = Review::query()->create($payload);
            $message = 'تم إرسال تقييمك بنجاح.';
        }

        $this->editingReviewId = $existing->id;
        $this->resetPage();

        $this->dispatch('close-modal', name: 'add-testimonial-modal');
        $this->dispatch('notify', message: $message);
    }

    public function render()
    {
        $settings = Setting::reviewSettings();
        $perPage = (int) $settings['per_page'];
        $client = authClient();
        $clientReview = $client instanceof Client ? $this->clientReviewFor($client) : null;

        $publishedQuery = Review::query()
            ->where('published', true)
            ->whereNotNull('rating');

        $total = (clone $publishedQuery)->count();
        $average = $total > 0
            ? round((float) (clone $publishedQuery)->avg('rating'), 1)
            : 0.0;

        /** @var Collection<int, object{rating: int, total: int}> $ratingRows */
        $ratingRows = Review::query()
            ->where('published', true)
            ->whereNotNull('rating')
            ->select('rating', DB::raw('count(*) as total'))
            ->groupBy('rating')
            ->get();

        $distribution = collect(range(5, 1))->mapWithKeys(
            fn (int $stars): array => [$stars => (int) ($ratingRows->firstWhere('rating', $stars)->total ?? 0)],
        );

        /** @var LengthAwarePaginator<int, Review> $reviews */
        $reviews = Review::query()
            ->where('published', true)
            ->with(['client:id,name,email,phone'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return tenantView('pages.reviews', [
            'sectionTitle' => $settings['section_title'],
            'reviews' => $reviews,
            'totalReviews' => $total,
            'averageRating' => $average,
            'distribution' => $distribution,
            'isClientAuthenticated' => $client instanceof Client,
            'hasClientReview' => $clientReview instanceof Review,
        ])->title($settings['section_title']);
    }

    private function loadReviewFormForClient(): void
    {
        $client = authClient();

        abort_unless($client instanceof Client, 403);

        $this->resetValidation();

        $existing = $this->clientReviewFor($client);

        if ($existing instanceof Review) {
            $this->editingReviewId = $existing->id;
            $this->title = (string) ($existing->title ?? '');
            $this->score = (string) ($existing->score ?? '');
            $this->rating = max(1, min(5, (int) ($existing->rating ?: 5)));

            return;
        }

        $this->editingReviewId = null;
        $this->reset(['title', 'score']);
        $this->rating = 5;
    }

    private function clientReviewFor(Client $client): ?Review
    {
        return Review::forClientAndContent($client->id, $this->contentId);
    }
}
