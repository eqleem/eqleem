<ui:container>
    <ui:mainbox title="{{ __('Manage page') }}"
        subtitle="{{ __('إدارة الصفحات والمحتوى،  .') }}">
        <x-slot:icon>
            <ui:icon name="message-2" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>
        {{-- Hide for now, #TODO, and flexable order based on entry type  --}}
        {{-- <x-slot:actions>
            <ui:button wire:click="addResponse" label="{{ __('New order') }}" icon="square-rounded-plus" />
        </x-slot:actions> --}}

        manage page page ..
        {{-- <livewire:admin.views.orders.table lazy /> --}}
    </ui:mainbox>
</ui:container>

 <?php
//  use Catalog\Subscription\Models\Plan;
 
 new class extends \Livewire\Component {
    //  public $tenant;
    //  public $currentPlanId;
 
    //  function mount()
    //  {
    //      $this->tenant = tenant();
    //      $this->currentPlanId = tenant('subscription.plan.id');
    //  }
 
    //  public function with()
    //  {
    //      return [
    //          // 'plans' => Plan::where('is_system', true)->with('features')->get(),
    //          'plans' => Plan::where('is_system', true)->where('active', true)->get(),
    //      ];
    //  }
 
    //  public function cancelSubscription()
    //  {
    //      tenant('subscription')->cancel();
    //      //  tenant('subscription')->suppress();
    //  }
 
     public function render()
     {
         return $this->view()->layout('admin::layout')->title(__('Manage page'));
     }
 }; ?>
