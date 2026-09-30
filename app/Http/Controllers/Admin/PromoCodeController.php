<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Models\Tour;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(Request $request): View
    {
        $query = PromoCode::with('tours')->withCount('tours');

        if ($search = $request->input('search')) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->input('discount_type'));
        }

        $promoCodes = $query->latest()->paginate(20);

        $activeCount = PromoCode::active()->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))->count();
        $expiredCount = PromoCode::whereNotNull('valid_until')->whereDate('valid_until', '<', today())->count();
        $usageCount = PromoCode::sum('used_count');

        return view('admin.promo-codes.index', compact(
            'promoCodes',
            'activeCount',
            'expiredCount',
            'usageCount',
        ));
    }

    public function create(): View
    {
        return view('admin.promo-codes.create', [
            'promoCode' => new PromoCode(['discount_type' => 'percentage', 'is_active' => true]),
            'tours' => Tour::orderBy('title')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        $promoCode = PromoCode::create($this->withNormalizedCode($validated));
        $promoCode->tours()->sync($request->input('tour_ids', []));

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code created successfully!');
    }

    public function edit(PromoCode $promoCode): View
    {
        return view('admin.promo-codes.edit', [
            'promoCode' => $promoCode,
            'tours' => Tour::orderBy('title')->get(),
        ]);
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $validated = $request->validate($this->rules($promoCode));

        $promoCode->update($this->withNormalizedCode($validated));
        $promoCode->tours()->sync($request->input('tour_ids', []));

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code updated successfully!');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promo code deleted successfully!');
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?PromoCode $promoCode = null): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('promo_codes', 'code')->ignore($promoCode?->id)],
            'description' => 'nullable|string|max:255',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => ['required', 'numeric', $this->discountValueRule()],
            'max_discount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'nullable|boolean',
            'tour_ids' => 'nullable|array',
            'tour_ids.*' => 'integer|exists:tours,id',
        ];
    }

    private function discountValueRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $type = request()->input('discount_type');

            if ($type === 'percentage' && (float) $value > 100) {
                $fail('পার্সেন্ট ছাড় ০ থেকে ১০০ এর মধ্যে হতে হবে।');
            }

            if ($type === 'fixed' && (float) $value <= 0) {
                $fail('নির্দিষ্ট টাকার ছাড় শূন্যের বেশি হতে হবে।');
            }
        };
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function withNormalizedCode(array $validated): array
    {
        $validated['code'] = Str::upper($validated['code']);
        $validated['is_active'] = (bool) request()->boolean('is_active');

        // A cap only makes sense for percentage discounts.
        if ($validated['discount_type'] !== 'percentage') {
            $validated['max_discount'] = null;
        }

        return $validated;
    }
}
