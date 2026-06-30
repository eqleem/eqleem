<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use App\Models\Link;

class VisitLink
{
    use AsAction;

    public function handle($id)
    {

        $link = Link::where('id', $id)->with('tenant:id,handle')->first();

        config()->set('tenant', $link->tenant);

        if($link) {
            // RequestAnalytics::create([
            //     'url' => $url,
            //     'tenant_id' => tenant()->id,
            // ]);

            return redirect()->to($link->link);
        }
 
    }
}
