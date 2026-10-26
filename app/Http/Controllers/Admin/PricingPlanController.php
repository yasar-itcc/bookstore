<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PricingPlanController extends Controller
{
    public function index(): View
    {
        return view('admin.pricing-plans.index', ['plans' => PricingPlan::orderBy('sort_order')->orderBy('price')->get()]);
    }

    public function create(): View
    {
        return view('admin.pricing-plans.form', ['plan' => new PricingPlan(['period' => 'month', 'is_active' => true, 'sort_order' => 0, 'features' => []])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $plan = PricingPlan::create($this->validated($request));

        return redirect()->route('admin.pricing-plans.index')->with('status', "Plan \"{$plan->name}\" created.");
    }

    public function edit(PricingPlan $pricingPlan): View
    {
        return view('admin.pricing-plans.form', ['plan' => $pricingPlan]);
    }

    public function update(Request $request, PricingPlan $pricingPlan): RedirectResponse
    {
        $pricingPlan->update($this->validated($request));

        return redirect()->route('admin.pricing-plans.index')->with('status', "Plan \"{$pricingPlan->name}\" updated.");
    }

    public function destroy(PricingPlan $pricingPlan): RedirectResponse
    {
        $pricingPlan->delete();

        return redirect()->route('admin.pricing-plans.index')->with('status', "Plan \"{$pricingPlan->name}\" deleted.");
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:0'],
            'period' => ['required', Rule::in(array_keys(PricingPlan::PERIODS))],
            'description' => ['nullable', 'string', 'max:300'],
            'features_text' => ['nullable', 'string', 'max:3000'],
            'button_text' => ['nullable', 'string', 'max:60'],
            'button_link' => ['nullable', 'string', 'max:255'],
            'is_highlighted' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        // One feature per line in the form.
        $data['features'] = collect(preg_split('/\r\n|\r|\n/', (string) ($data['features_text'] ?? '')))->map(fn ($line) => trim($line))->filter()->values()->all();
        $data['sort_order'] ??= 0;
        unset($data['features_text']);

        return $data;
    }
}
