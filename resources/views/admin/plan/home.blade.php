 <ui:container>
     <ui:mainbox title="إدارة الإشتراك" subtitle="للمعلومات ولإدارة الاشتراك الخاص بهذه الصفحة.">
         <x-slot:icon>
             <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
                 <path d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round"></path>
                 <path opacity=".4" d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7" stroke="currentColor"
                     stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
             </svg>

         </x-slot:icon>
     </ui:mainbox>

     @if (session('status'))
         <ui:alert class="mt-10" color="{{ session('color', 'green') }}">
             {{ session('status') }}
         </ui:alert>
     @endif

    plan page .. 
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
         return $this->view()->layout('admin::layout')->title(__('Subscription'));
     }
 }; ?>
