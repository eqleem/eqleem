<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\Tenant;
use App\Support\ContentTypeRegistry;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncTenantSections
{
    use AsAction;

    /**
     * @param  list<string>  $enabled
     */
    public function handle(Tenant $tenant, array $enabled, ?ContentTypeRegistry $contentTypes = null): void
    {
        $contentTypes ??= app(ContentTypeRegistry::class);
        $enabled = array_values(array_unique($enabled));

        foreach ($contentTypes->managedSections() as $contentType) {
            $isEnabled = in_array($contentType->slug, $enabled, true);

            if ($isEnabled) {
                EnsureSectionBlockLink::run($tenant->id, $contentType->slug);
                $this->reactivateManagedBlocks($tenant->id, $contentType->slug);

                continue;
            }

            $this->deleteManagedBlocks($tenant->id, $contentType->slug);
        }
    }

    protected function reactivateManagedBlocks(int $tenantId, string $contentTypeSlug): void
    {
        $this->sectionBlocks($tenantId, $contentTypeSlug)
            ->filter(fn (Block $block): bool => (bool) data_get($block->data, 'disabled_by_section_manager'))
            ->each(function (Block $block): void {
                $data = is_array($block->data) ? $block->data : [];
                $data['managed_section'] = true;
                unset($data['disabled_by_section_manager']);

                $block->update([
                    'active' => true,
                    'data' => $data,
                ]);
            });
    }

    protected function deleteManagedBlocks(int $tenantId, string $contentTypeSlug): void
    {
        $this->sectionBlocks($tenantId, $contentTypeSlug)
            ->each(function (Block $block): void {
                $block->delete();
            });
    }

    /**
     * @return Collection<int, Block>
     */
    protected function sectionBlocks(int $tenantId, string $contentTypeSlug): Collection
    {
        return Block::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereNull('parent_id')
            ->whereNull('content_id')
            ->where('is_default', false)
            ->where('type', 'block-link')
            ->where('data->link_type', 'section')
            ->where('data->content_type', $contentTypeSlug)
            ->get();
    }
}
