<?php

namespace App\API\OnDemandServices\Concerns;

use App\Models\Content;
use App\Support\OnDemandUnit;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesOnDemandService
{
    protected function onDemandServiceType(): string
    {
        return contentTypeModel('on-demand-services');
    }

    protected function findOnDemandService(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->onDemandServiceType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueOnDemandServiceSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'on-demand-service';
        $counter = 1;

        while (
            Content::query()
                ->where('slug', $slug)
                ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    protected function slugifyTitle(string $title): string
    {
        $slug = Str::slug($title);

        return $slug !== '' ? $slug : 'on-demand-service';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/on-demand-services/';
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    protected function unitOptions(): array
    {
        return collect(OnDemandUnit::options())
            ->map(fn (string $label, string $id): array => [
                'id' => $id,
                'label' => $label,
            ])
            ->values()
            ->all();
    }
}
