<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\ClientPaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();
        $methods = ClientPaymentMethod::where('client_id', $client->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'         => true,
            'payment_methods' => PaymentMethodResource::collection($methods),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_type'   => 'required|string|in:gcash,maya,bank_transfer,credit_card,debit_card',
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        $client = $request->user();
        $isDefault = $request->boolean('is_default');

        $existingCount = ClientPaymentMethod::where('client_id', $client->id)->count();
        if ($existingCount === 0) {
            $isDefault = true;
        }

        if ($isDefault) {
            ClientPaymentMethod::where('client_id', $client->id)->update(['is_default' => false]);
        }

        $method = ClientPaymentMethod::create([
            'client_id'      => $client->id,
            'payment_type'   => $validated['payment_type'],
            'provider_name'  => $validated['provider_name'],
            'account_name'   => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'is_default'     => $isDefault,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success'        => true,
            'message'        => 'Payment method added successfully.',
            'payment_method' => new PaymentMethodResource($method),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $client = $request->user();
        $method = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);

        $validated = $request->validate([
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            ClientPaymentMethod::where('client_id', $client->id)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $method->update([
            'provider_name'  => $validated['provider_name'],
            'account_name'   => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'is_default'     => $isDefault,
            'notes'          => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success'        => true,
            'message'        => 'Payment method updated successfully.',
            'payment_method' => new PaymentMethodResource($method),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $client = $request->user();
        $method = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);
        $wasDefault = $method->is_default;

        $method->delete();

        if ($wasDefault) {
            $another = ClientPaymentMethod::where('client_id', $client->id)->first();
            if ($another) {
                $another->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment method removed successfully.',
        ]);
    }

    public function setDefault(Request $request, $id)
    {
        $client = $request->user();
        $method = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);

        ClientPaymentMethod::where('client_id', $client->id)->update(['is_default' => false]);
        $method->update(['is_default' => true]);

        return response()->json([
            'success'        => true,
            'message'        => 'Set as default payment method.',
            'payment_method' => new PaymentMethodResource($method),
        ]);
    }
}
