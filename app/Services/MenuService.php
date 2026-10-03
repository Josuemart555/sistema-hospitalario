<?php

namespace App\Services;

use App\Models\Option;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    public function for(User $user): Collection
    {
        $version = Cache::get('menu.version', 1);

        $items = Cache::remember("menu.{$version}.user.{$user->id}", now()->addHours(12), function () use ($user): array {
            $roleIds = $user->roles()->pluck('roles.id');
            $assigned = Option::query()->where('is_active', true)
                ->where(function ($query) use ($user, $roleIds): void {
                    $query->whereHas('users', fn ($users) => $users->whereKey($user->id))
                        ->orWhereHas('roles', fn ($roles) => $roles->whereIn('roles.id', $roleIds));
                })->with('permission')->get()
                ->filter(fn (Option $option): bool => $option->permission === null || $user->can($option->permission->name));

            $parents = Option::query()->where('is_active', true)->whereKey($assigned->pluck('parent_id')->filter()->unique())
                ->with('permission')->get()
                ->filter(fn (Option $option): bool => $option->permission === null || $user->can($option->permission->name));
            $all = $assigned->merge($parents)->unique('id');

            return $all->whereNull('parent_id')->sortBy('sort_order')->map(function (Option $parent) use ($all): array {
                $children = $all->where('parent_id', $parent->id)->sortBy('sort_order')->map(fn (Option $child): array => $this->toArray($child))->values()->all();

                return [...$this->toArray($parent), 'children' => $children];
            })->filter(fn (array $option): bool => $option['route_name'] !== null || $option['children'] !== [])->values()->all();
        });

        return collect($items)->map(fn (array $item): object => (object) [...$item, 'children' => collect($item['children'])->map(fn (array $child): object => (object) $child)]);
    }

    public function flush(): void
    {
        Cache::forever('menu.version', ((int) Cache::get('menu.version', 1)) + 1);
    }

    /** @return array{name: string, route_name: ?string, icon: string} */
    private function toArray(Option $option): array
    {
        return ['name' => $option->name, 'route_name' => $option->route_name, 'icon' => $option->icon];
    }
}
