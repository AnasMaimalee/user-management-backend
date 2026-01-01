<?php

namespace App\Http\Controllers\Api\Payroll;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollExport;

class PayrollExportController extends Controller
{
    public function pdf(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $payrolls = Payroll::with('employee')
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->orderBy('created_at')
            ->get();

        if ($payrolls->isEmpty()) {
            return response()->json(['message' => 'No payroll data found for selected period'], 404);
        }

        $monthName = date('F', mktime(0, 0, 0, $request->month, 1));
        $data = [
            'payrolls' => $payrolls,
            'month' => $monthName,
            'year' => $request->year,
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.payroll-pdf', $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download("Payroll-{$monthName}-{$request->year}.pdf");
    }

    public function excel(Request $request)
    {
        $request->validate([
            'year' => 'required|integer',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $monthName = date('F', mktime(0, 0, 0, $request->month, 1));

        $payrolls = Payroll::with('employee')
            ->where('year', $request->year)
            ->where('month', $request->month)
            ->exists();

        if (!$payrolls) {
            return response()->json(['message' => 'No payroll data found for selected period'], 404);
        }

        return Excel::download(
            new PayrollExport($request->year, $request->month),
            "Payroll-{$monthName}-{$request->year}.xlsx"
        );
    }
}
