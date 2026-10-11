<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        $promos = Promo::orderByDesc('start_date')->orderByDesc('id')->get();
        $editPromo = $request->filled('edit_id') ? Promo::find($request->integer('edit_id')) : null;

        return view('admin.promos.index', compact('promos', 'editPromo'));
    }

    public function store(Request $request)
    {
        $promoId = $request->integer('promo_id');
        $validated = $request->validate([
            'promo_id' => 'nullable|integer|exists:promos,id',
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:1000',
            'discount_percent' => 'required|numeric|gt:0|lte:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        unset($validated['promo_id']);
        if ($promoId > 0) {
            $promo = Promo::findOrFail($promoId);
            $promo->update($validated);
            $message = "Promo '{$promo->title}' updated.";
        } else {
            $promo = Promo::create($validated);
            $message = "Promo '{$promo->title}' created.";
        }

        return redirect()->route('admin.promos')->with('success_message', $message);
    }

    public function destroy(Promo $promo)
    {
        $promo->delete();

        return redirect()->route('admin.promos')->with('success_message', 'Promo deleted.');
    }
}
