<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Models\ClientPaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        ClientPaymentMethod::ensureTableExists();
    }

    public function index(Request $request): JsonResponse
    {
        ClientPaymentMethod::ensureTableExists();

        try {
            $methods = ClientPaymentMethod::where('client_id', $request->user()->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => PaymentMethodResource::collection($methods),
            ]);
        } catch (\Throwable $e) {
            Log::error('API PaymentMethod index error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'data'    => [],
            ]);
        }
    }

    public function store(Request $request): JsonResponse
    {
        ClientPaymentMethod::ensureTableExists();

        $validated = $request->validate([
            'payment_type'   => 'required|string|in:gcash,maya,bank_transfer,credit_card,debit_card',
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $clientId = $request->user()->id;
            $isDefault = $request->boolean('is_default');

            $existingCount = ClientPaymentMethod::where('client_id', $clientId)->count();
            if ($existingCount === 0) {
                $isDefault = true;
            }

            if ($isDefault) {
                ClientPaymentMethod::where('client_id', $clientId)->update(['is_default' => false]);
            }

            $method = ClientPaymentMethod::create([
                'client_id'      => $clientId,
                'payment_type'   => $validated['payment_type'],
                'provider_name'  => $validated['provider_name'],
                'account_name'   => $validated['account_name'],
                'account_number' => $validated['account_number'],
                'is_default'     => $isDefault,
                'notes'          => $validated['notes'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method created successfully.',
                'data'    => new PaymentMethodResource($method),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('API PaymentMethod store error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save payment method: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        ClientPaymentMethod::ensureTableExists();

        $method = ClientPaymentMethod::where('client_id', $request->user()->id)->find($id);
        if (!$method) {
            return response()->json(['success' => false, 'message' => 'Payment method not found.'], 404);
        }

        $validated = $request->validate([
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $isDefault = $request->boolean('is_default');
            if ($isDefault) {
                ClientPaymentMethod::where('client_id', $request->user()->id)
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
                'success' => true,
                'message' => 'Payment method updated successfully.',
                'data'    => new PaymentMethodResource($method->fresh()),
            ]);
        } catch (\Throwable $e) {
            Log::error('API PaymentMethod update error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        ClientPaymentMethod::ensureTableExists();

        $method = ClientPaymentMethod::where('client_id', $request->user()->id)->find($id);
        if (!$method) {
            return response()->json(['success' => false, 'message' => 'Payment method not found.'], 404);
        }

        try {
            $wasDefault = $method->is_default;
            $method->delete();

            if ($wasDefault) {
                $another = ClientPaymentMethod::where('client_id', $request->user()->id)->first();
                if ($another) {
                    $another->update(['is_default' => true]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            Log::error('API PaymentMethod destroy error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function setDefault(Request $request, $id): JsonResponse
    {
        ClientPaymentMethod::ensureTableExists();

        $method = ClientPaymentMethod::where('client_id', $request->user()->id)->find($id);
        if (!$method) {
            return response()->json(['success' => false, 'message' => 'Payment method not found.'], 404);
        }

        try {
            ClientPaymentMethod::where('client_id', $request->user()->id)->update(['is_default' => false]);
            $method->update(['is_default' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Default payment method updated.',
                'data'    => new PaymentMethodResource($method->fresh()),
            ]);
        } catch (\Throwable $e) {
            Log::error('API PaymentMethod setDefault error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update default method: ' . $e->getMessage(),
            ], 500);
        }
    }
}
