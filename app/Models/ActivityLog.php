<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Spatie\Activitylog\Models\Activity;

class ActivityLog extends Activity
{
    use BelongsToTenant;
}
