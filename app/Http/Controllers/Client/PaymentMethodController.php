<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();
        $paymentMethods = ClientPaymentMethod::where('client_id', $client->id)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('client.payment-methods', compact('client', 'paymentMethods'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'payment_type'   => 'required|string|in:gcash,maya,bank_transfer,credit_card,debit_card',
            'provider_name'  => 'required|string|max:100',
            'account_name'   => 'required|string|max:150',
            'account_number' => 'required|string|max:100',
            'is_default'     => 'nullable|boolean',
            'notes'          => 'nullable|string|max:500',
        ]);

        $client = Auth::guard('client')->user();
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
    }

    public function update(Request $request, $id)
    {
        $client = Auth::guard('client')->user();
        $paymentMethod = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);

        $request->validate([
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

        $paymentMethod->update([
            'provider_name'  => $request->provider_name,
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'is_default'     => $isDefault,
            'notes'          => $request->notes,
        ]);

        return redirect()->route('client.payment-methods')
            ->with('success_message', 'Payment method updated successfully.');
    }

    public function destroy($id)
    {
        $client = Auth::guard('client')->user();
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
    }

    public function setDefault($id)
    {
        $client = Auth::guard('client')->user();
        $paymentMethod = ClientPaymentMethod::where('client_id', $client->id)->findOrFail($id);

        ClientPaymentMethod::where('client_id', $client->id)->update(['is_default' => false]);
        $paymentMethod->update(['is_default' => true]);

        return redirect()->route('client.payment-methods')
            ->with('success_message', 'Set ' . $paymentMethod->provider_name . ' as your default payment method.');
    }
}
