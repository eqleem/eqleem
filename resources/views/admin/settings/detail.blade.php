<ui:container>
    detail page ..
</ui:container>

 <?php

 new class extends \Livewire\Component {

    public function render()
    {
        return $this->view()->layout('admin::layout')->title(__('Settings'));
    }
 }; ?>