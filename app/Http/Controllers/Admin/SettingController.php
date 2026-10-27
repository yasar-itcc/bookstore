<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Setting;
use App\Support\ImageTrimmer;
use App\Support\SettingsSchema;
use App\Support\Themes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Store info, Email and SEO under Admin → Settings. PageContentController reuses this for the
 * Home, About and Pricing page text under Content → Pages.
 */
class SettingController extends Controller
{
    /**
     * Which SettingsSchema groups this screen edits, and the route names of the editor.
     */
    protected string $scope = 'settings';

    protected string $route = 'admin.settings';

    public function edit(?string $group = null): View
    {
        $groups = SettingsSchema::groups($this->scope);
        $group ??= array_key_first($groups);
        abort_unless(isset($groups[$group]), 404);

        return view('admin.settings.edit', [
            'groups' => $groups,
            'group' => $group,
            'scope' => $this->scope,
            'route' => $this->route,
            'categories' => Category::orderBy('name')->pluck('name', 'id')->all(),
            'books' => Book::orderBy('title')->pluck('title', 'id')->all(),
        ]);
    }

    public function update(Request $request, string $group): RedirectResponse
    {
        abort_unless(isset(SettingsSchema::groups($this->scope)[$group]), 404);
        $fields = SettingsSchema::fields($group);

        $request->validate($this->rules($fields));

        $values = [];
        foreach ($fields as $key => [$label, $type]) {
            $input = str_replace('.', '__', $key);

            $values[$key] = match ($type) {
                'toggle' => $request->boolean($input) ? '1' : '0',
                'image' => $this->image($request, $input, $key),
                'features' => collect($request->input($input, []))
                    ->filter(fn ($item) => filled($item['title'] ?? null))
                    ->map(fn ($item) => ['icon' => $item['icon'] ?: 'ti-star', 'title' => $item['title'], 'text' => $item['text'] ?? ''])
                    ->all(),
                'team' => $this->team($request, $input),
                'theme' => $request->input($input) ?: Setting::get($key),
                default => $request->input($input),
            };
        }

        Setting::put($values);

        $label = SettingsSchema::groups()[$group]['label'];

        return redirect()->route("{$this->route}.edit", $group)->with('status', $this->scope === 'pages' ? "{$label} saved." : "{$label} settings saved.");
    }

    private function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $key => [$label, $type, $required]) {
            $input = str_replace('.', '__', $key);
            $base = $required === 'required' ? ['required'] : ['nullable'];

            $rules[$input] = match ($type) {
                'email' => [...$base, 'email', 'max:150'],
                'url' => [...$base, 'url', 'max:255'],
                'number' => [...$base, 'numeric', 'min:0'],
                'toggle' => ['nullable', 'boolean'],
                'image' => ['nullable', 'image', 'max:2048'],
                'category' => ['nullable', 'exists:categories,id'],
                'theme' => ['nullable', Rule::in(array_keys(Themes::all()))],
                'book' => ['nullable', 'exists:books,id'],
                'features', 'team' => ['nullable', 'array', 'max:8'],
                'textarea' => [...$base, 'string', 'max:3000'],
                default => [...$base, 'string', 'max:255'],
            };

            if ($type === 'team') {
                $rules["{$input}.*.name"] = ['nullable', 'string', 'max:80'];
                $rules["{$input}.*.role"] = ['nullable', 'string', 'max:80'];
                $rules["{$input}.*.photo_file"] = ['nullable', 'image', 'max:2048'];
            }
            if ($type === 'features') {
                $rules["{$input}.*.title"] = ['nullable', 'string', 'max:60'];
                $rules["{$input}.*.text"] = ['nullable', 'string', 'max:120'];
                $rules["{$input}.*.icon"] = ['nullable', 'string', 'max:40', 'regex:/^ti-[a-z0-9-]+$/'];
            }
        }

        return $rules;
    }

    /**
     * Keep, replace or remove an uploaded image setting.
     */
    private function image(Request $request, string $input, string $key): ?string
    {
        $current = Setting::get($key);

        if ($request->hasFile($input) || $request->boolean("{$input}_remove")) {
            if ($current) {
                Storage::disk('public')->delete($current);
            }

            $path = $request->file($input)?->store('settings', 'public');
            if ($path && str_starts_with($key, 'store.logo')) {
                ImageTrimmer::trimTransparent($path);
            }

            return $path;
        }

        return $current;
    }

    private function team(Request $request, string $input): array
    {
        return collect($request->input($input, []))
            ->map(function ($member, $index) use ($request, $input) {
                if (! filled($member['name'] ?? null)) {
                    return null;
                }
                $photo = $member['photo'] ?? null;
                if ($file = $request->file("{$input}.{$index}.photo_file")) {
                    $photo = $file->store('settings/team', 'public');
                }

                return ['name' => $member['name'], 'role' => $member['role'] ?? '', 'photo' => $photo];
            })
            ->filter()
            ->values()
            ->all();
    }
}
