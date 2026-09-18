<?php

namespace App\Models;

use Database\Factories\OptionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Option extends Model
{
    /** @use HasFactory<OptionFactory> */
    use HasFactory;

    protected $fillable = ['parent_id', 'permission_id', 'name', 'route_name', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'option_role');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'option_user');
    }

    /**
     * Build the full, unfiltered options hierarchy for admin pickers.
     *
     * @return array<int, array{id: int, name: string, children: array<mixed>}>
     */
    public static function tree(): array
    {
        $all = self::query()->orderBy('sort_order')->orderBy('name')->get();

        return self::branch($all, null);
    }

    /**
     * @param  Collection<int, self>  $all
     * @return array<int, array{id: int, name: string, children: array<mixed>}>
     */
    private static function branch(Collection $all, ?int $parentId): array
    {
        return $all->where('parent_id', $parentId)
            ->map(fn (self $option): array => [
                'id' => $option->id,
                'name' => $option->name,
                'children' => self::branch($all, $option->id),
            ])
            ->values()
            ->all();
    }
}
