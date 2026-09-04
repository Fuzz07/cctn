<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    public function __construct()
    {
        ClientPaymentMethod::ensureTableExists();
    }

    public function index()
    {
        ClientPaymentMethod::ensureTableExists();

        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('login')->with('error_message', 'Please sign in to access your payment methods.');
        }

        try {
            $paymentMethods = ClientPaymentMethod::where('client_id', $client->id)
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Throwable $e) {
            Log::error('Failed to load payment methods: ' . $e->getMessage());
            $paymentMethods = collect();
        }

        return view('client.payment-methods', compact('client', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        ClientPaymentMethod::ensureTableExists();

        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('login');
        }

        $request->validate([
            'payment_type'   => 'required|string|in:gcash,maya,bank_transfer,credit_card,debit_card',
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $isDefault = $request->boolean('is_default');

            // If this is the client's first payment method, make it default automatically
            $existingCount = ClientPaymentMethod::where('client_id', $client->id)->count();
            if ($existingCount === 0) {
                $isDefault = true;
            }

            if ($isDefault) {
                ClientPaymentMethod::where('client_id', $client->id)->update(['is_default' => false]);
            }

            ClientPaymentMethod::create([
                'client_id'      => $client->id,
                'payment_type'   => $request->payment_type,
                'provider_name'  => $request->provider_name,
                'account_name'   => $request->account_name,
                'account_number' => $request->account_number,
                'is_default'     => $isDefault,
                'notes'          => $request->notes,
            ]);

            return redirect()->route('client.payment-methods')
                ->with('success_message', 'Payment method added successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to store payment method: ' . $e->getMessage());
            return redirect()->route('client.payment-methods')
                ->with('error_message', 'Could not save payment method: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        ClientPaymentMethod::ensureTableExists();

        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('login');
        }

        $request->validate([
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        try {
            $paymentMethod = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);

            $isDefault = $request->boolean('is_default');
            if ($isDefault) {
                ClientPaymentMethod::where('client_id', $client->id)
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $paymentMethod->update([
                'provider_name'  => $request->provider_name,
                'account_name'   => $request->account_name,
                'account_number' => $request->account_number,
                'is_default'     => $isDefault,
                'notes'          => $request->notes,
            ]);

            return redirect()->route('client.payment-methods')
                ->with('success_message', 'Payment method updated successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to update payment method: ' . $e->getMessage());
            return redirect()->route('client.payment-methods')
                ->with('error_message', 'Could not update payment method: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        ClientPaymentMethod::ensureTableExists();

        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('login');
        }

        try {
            $paymentMethod = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);
            $wasDefault = $paymentMethod->is_default;

            $paymentMethod->delete();

            // If default was deleted, promote another method to default if any exists
            if ($wasDefault) {
                $another = ClientPaymentMethod::where('client_id', $client->id)->first();
                if ($another) {
                    $another->update(['is_default' => true]);
                }
            }

            return redirect()->route('client.payment-methods')
                ->with('success_message', 'Payment method removed successfully.');
        } catch (\Throwable $e) {
            Log::error('Failed to delete payment method: ' . $e->getMessage());
            return redirect()->route('client.payment-methods')
                ->with('error_message', 'Could not remove payment method: ' . $e->getMessage());
        }
    }

    public function setDefault($id)
    {
        ClientPaymentMethod::ensureTableExists();

        $client = Auth::guard('client')->user();
        if (!$client) {
            return redirect()->route('login');
        }

        try {
            $paymentMethod = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);

            ClientPaymentMethod::where('client_id', $client->id)->update(['is_default' => false]);
            $paymentMethod->update(['is_default' => true]);

            return redirect()->route('client.payment-methods')
                ->with('success_message', 'Set ' . $paymentMethod->provider_name . ' as your default payment method.');
        } catch (\Throwable $e) {
            Log::error('Failed to set default payment method: ' . $e->getMessage());
            return redirect()->route('client.payment-methods')
                ->with('error_message', 'Could not update default payment method: ' . $e->getMessage());
        }
    }
}
