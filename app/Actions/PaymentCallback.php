<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Plan;

class PaymentCallback
{
    use AsAction, WithAttributes;

    public function rules(): array
    {
        return [ 'id' => 'required|string' ];
    }

    public function handle(Request $request)
    {
        $this->fill($request->all());

        $validatedData = $this->validateAttributes();
 
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(config('services.moyasar.secret_key')),
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->get(config('services.moyasar.base_url') . 'payments/' . $validatedData['id'])->json();
    
        // $payment = Payment::where('payment_id', $validatedData['id'])->first();

        // if($payment) {
        //     $payment->setStatus(data_get($response, 'status'));
 
        //     $payment->description = data_get($response, 'description');
        //     $payment->ip = data_get($response, 'ip');
        //     $payment->meta = $response;
        //     $payment->save();

        //     if(data_get($response, 'status') == 'paid') {
        //         return $this->subscriptTo(data_get($payment, 'purchased_id'));
        //     }  

        //     // return redirect()->route('admin.subscription.index')->with('success', 'تم تفعيل الباقة بنجاح');
        // } 
        
        if(data_get($response, 'status') == 'paid') {
            return $this->subscriptTo(data_get($response, 'metadata.plan_id'));
        }  

        session()->flash('color', 'red');
        session()->flash('status', __('عملية الدفع فشلت، الرجاء المحاولة مرة أخرى'));

        return redirect()->route('admin.home');
    }


    function subscriptTo($planId)
    {
        $plan = Plan::whereId($planId)->where('is_system', true)->first();

        if (auth()->user()->tenant->subscription) {
            auth()->user()->tenant->switchTo($plan, immediately: false);
        } else {
            auth()->user()->tenant->subscribeTo($plan);
        }

        // \Mail::to(user('email'))->send(new \Catalog\Subscription\Mails\NewSubscription(user(), $this->tenant));

        session()->flash('status', __('Subscription plan updated successfully.') );

        return redirect(route('admin.home').'/subscription');
    }
    
}
