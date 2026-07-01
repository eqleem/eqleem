<ui:container>
    manage page page ..  
</ui:container>

 <?php

 new class extends \Livewire\Component {
     public function render()
     {
         return $this->view()->layout('admin::layout')->title(__('Manage page'));
     }
 }; ?>
