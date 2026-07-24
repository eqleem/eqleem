<?php

namespace App\Actions;

use App\Models\Link;
use Lorisleiva\Actions\Concerns\AsAction;

class VisitLink
{
    use AsAction;

    public function handle(int|string $id)
    {
        $link = Link::query()->where('id', $id)->with('tenant:id,handle')->first();

        config()->set('tenant', $link->tenant);

        if (! $link) {
            return;
        }

        return redirect()->to($link->link);
    }
}
