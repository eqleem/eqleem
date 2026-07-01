<ui:container>
    <ui:mainbox title="{{ __('Account info') }}" subtitle="{{ __('Manager your account info.') }}">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5"
                    stroke-linecap="round" stroke-linejoin="round"></path>
                <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
        </x-slot:icon>

        <ui:form wire:submit="submit">
            <ui:input name="name" label="{{ __('Name') }}" placeholder="{{ __('Name') }}" />
            <ui:input name="email" label="{{ __('Email') }}" placeholder="your@email.com" dir="ltr" />

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot:footer>
        </ui:form>
    </ui:mainbox>
</ui:container>


<?php
new class extends \Livewire\Component {
    public $title = 'Account info';
    public $name;
    public $email;

    function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . auth()->id()],
        ];
    }

    function submit()
    {
        $this->validate();

        auth()->user()->name = $this->name;
        auth()->user()->email = $this->email;
        auth()->user()->save();

        //  cache()->forget('auth.user'); // refactor later

        $this->dispatch('notify', text: __('Account info updated successfully.'));
    }

    public function rendering($view): void
    {
        $view->title(__('Members'))->layout('admin::layout');
    }
}; ?>
