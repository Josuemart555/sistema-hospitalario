<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOptionRequest;
use App\Models\Option;
use App\Services\MenuService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Permission;

class OptionController extends Controller
{
    public function index(): View
    {
        return view('admin.options.index', ['options' => Option::with(['parent', 'permission'])->orderBy('sort_order')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.options.form', $this->formData());
    }

    public function store(StoreOptionRequest $request, MenuService $menu): RedirectResponse
    {
        Option::create($this->validated($request));
        $menu->flush();

        return redirect()->route('admin.options.index')->with('success', 'Opción creada correctamente.');
    }

    public function show(Option $option): RedirectResponse
    {
        return redirect()->route('admin.options.edit', $option);
    }

    public function edit(Option $option): View
    {
        return view('admin.options.form', [...$this->formData($option), 'option' => $option]);
    }

    public function update(StoreOptionRequest $request, Option $option, MenuService $menu): RedirectResponse
    {
        $option->update($this->validated($request));
        $menu->flush();

        return redirect()->route('admin.options.index')->with('success', 'Opción actualizada correctamente.');
    }

    public function destroy(Option $option, MenuService $menu): RedirectResponse
    {
        $option->delete();
        $menu->flush();

        return back()->with('success', 'Opción eliminada.');
    }

    private function formData(?Option $current = null): array
    {
        return ['parents' => Option::whereNull('parent_id')->when($current, fn ($query) => $query->whereKeyNot($current->id))->orderBy('name')->get(), 'permissions' => Permission::orderBy('name')->get()];
    }

    private function validated(StoreOptionRequest $request): array
    {
        return [...$request->safe()->except('is_active'), 'is_active' => $request->boolean('is_active')];
    }
}
