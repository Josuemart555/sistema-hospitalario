<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(Option::class, 'option_role');
    }
}
