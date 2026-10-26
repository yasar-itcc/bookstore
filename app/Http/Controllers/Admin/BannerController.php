<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BannerController extends Controller
{
    public function index(): View
    {
        $banners = Banner::orderBy('placement')->orderBy('sort_order')->orderBy('id')->get()->groupBy('placement');

        return view('admin.banners.index', compact('banners'));
    }

    public function create(Request $request): View
    {
        $placement = array_key_exists($request->placement, Banner::PLACEMENTS) ? $request->placement : 'hero';

        return view('admin.banners.form', ['banner' => new Banner(['placement' => $placement, 'is_active' => true, 'sort_order' => 0])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $banner = Banner::create($this->validated($request));

        return redirect()->route('admin.banners.index')->with('status', "Banner \"{$banner->title}\" created.");
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $banner->update($this->validated($request, $banner));

        return redirect()->route('admin.banners.index')->with('status', "Banner \"{$banner->title}\" updated.");
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('status', "Banner \"{$banner->title}\" deleted.");
    }

    private function validated(Request $request, ?Banner $banner = null): array
    {
        $data = $request->validate([
            'placement' => ['required', Rule::in(array_keys(Banner::PLACEMENTS))],
            'eyebrow' => ['nullable', 'string', 'max:60'],
            'title' => ['required', 'string', 'max:150'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:4096'],
            'remove_image' => ['boolean'],
        ]);

        $data['sort_order'] ??= 0;

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($banner?->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $data['image'] = $request->file('image')?->store('banners', 'public');
        }
        unset($data['remove_image']);

        return $data;
    }
}
