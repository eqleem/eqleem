<x-tenant-theme::module-layout>
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => $form->title]]" />
    </section>

    <section class="mt-8">
        <div class="mx-auto max-w-7xl px-2">
            <div class="mx-auto max-w-2xl text-center">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl">{{ $form->title }}</h1>
                @if ($description !== '')
                    <p class="mt-4 text-base font-normal leading-7 text-gray-600">{{ $description }}</p>
                @endif
            </div>

            <div class="mx-auto mt-10 max-w-xl rounded-2xl bg-white p-5 sm:p-8">
                <livewire:tenant.forms.submit
                    :form-content-id="$form->id"
                    :description="''"
                    :fields="$fields"
                    :key="'form-detail-submit-'.$form->id"
                />
            </div>
        </div>
    </section>
</x-tenant-theme::module-layout>
