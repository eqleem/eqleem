<?php

namespace App\API\Forms\Concerns;

use App\Models\Content;
use App\Support\FormField;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesForm
{
    protected function formType(): string
    {
        return contentTypeModel('forms');
    }

    protected function findForm(string $uuid): Content
    {
        $content = Content::query()
            ->type($this->formType())
            ->where('uuid', $uuid)
            ->first();

        if (! $content instanceof Content) {
            throw new NotFoundHttpException;
        }

        return $content;
    }

    protected function uniqueFormSlug(string $baseSlug, ?int $exceptId = null): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'form';
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

        return $slug !== '' ? $slug : 'form';
    }

    protected function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/forms/';
    }

    /**
     * @return array<string, string>
     */
    protected function fieldTypeOptions(): array
    {
        return FormField::typeOptions();
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     */
    protected function validateUniqueFieldNames(array $fields): void
    {
        $names = collect($fields)->pluck('name')->filter();

        if ($names->count() !== $names->unique()->count()) {
            throw ValidationException::withMessages([
                'fields' => 'يجب أن تكون أسماء الحقول (name) فريدة داخل النموذج.',
            ]);
        }
    }
}
