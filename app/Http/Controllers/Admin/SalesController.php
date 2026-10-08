<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Client;
use App\Support\TableSort;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $sort = TableSort::resolve($request, [
            'receipt' => 'receipt_no',
            'client'  => function ($q, $dir) {
                return $q->orderBy(
                    Client::select('lastname')->whereColumn('clients.id', 'payments.client_id'), $dir
                )->orderBy(
                    Client::select('firstname')->whereColumn('clients.id', 'payments.client_id'), $dir
                );
            },
            'amount'  => 'amount_paid',
            'method'  => 'payment_method',
            'date'    => 'payment_date',
        ], 'date', 'desc');

        $query = Payment::with(['client', 'billing']);
        TableSort::apply($query, $sort);

        $payments = $query->orderBy('id', 'desc')
            ->simplePaginate(10)
            ->withQueryString();
        $totalRevenue = Payment::sum('amount_paid');
        $paymentCount = Payment::count();
        $lastPayment = Payment::orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return view('admin.sales', compact(
            'payments',
            'totalRevenue',
            'paymentCount',
            'lastPayment',
            'sort'
        ));
    }

    public function receipt($id)
    {
        $payment = Payment::with(['client.currentAppointment', 'billing'])->findOrFail($id);
        return view('admin.sales.receipt', compact('payment'));
    }

    public function printSummary(Request $request)
    {
        $payments = Payment::with(['client', 'billing'])->orderBy('payment_date', 'desc')->get();
        $totalRevenue = $payments->sum('amount_paid');

        return view('admin.sales.summary-print', compact('payments', 'totalRevenue'));
    }
}
