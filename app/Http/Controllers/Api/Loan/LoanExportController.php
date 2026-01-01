<?php

namespace App\Http\Controllers\Api\Loan;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LoanExport;

class LoanExportController extends Controller
{
    private function getFilteredLoans(Request $request)
    {
        $request->validate([
            'year'          => 'nullable|integer',
            'month'         => 'nullable|integer|min:1|max:12',
            'employee_id'   => 'nullable|uuid|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'status'        => 'nullable|in:pending,approved,rejected,completed',
        ]);

        $query = Loan::with(['employee.department'])->latest('updated_at');

        // If no status sent → this is from History table → exclude pending
        if (!$request->filled('status')) {
            $query->whereNot('status', 'pending');
        }

        // If status sent (from Pending table) → use it
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply common filters
        if ($request->filled('year')) {
            $year = $request->year;
            $query->where(function ($q) use ($year) {
                $q->whereYear('created_at', $year)
                    ->orWhereYear('approved_at', $year)
                    ->orWhereYear('updated_at', $year);
            });
        }

        if ($request->filled('month') && $request->filled('year')) {
            $month = $request->month;
            $query->where(function ($q) use ($month) {
                $q->whereMonth('created_at', $month)
                    ->orWhereMonth('approved_at', $month)
                    ->orWhereMonth('updated_at', $month);
            });
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        $loans = $query->get();

        if ($loans->isEmpty()) {
            abort(404, 'No records found');
        }

        $loans->transform(fn($loan) => $loan->append('paid_amount'));

        return $loans;
    }

    private function getFilename(Request $request, $ext)
    {
        $parts = ['Loan-Report'];

        if ($request->filled('month') && $request->filled('year')) {
            $parts[] = date('F', mktime(0,0,0,$request->month,1));
            $parts[] = $request->year;
        } elseif ($request->filled('year')) {
            $parts[] = $request->year;
        }

        if ($request->filled('status')) {
            $parts[] = ucfirst($request->status);
        }

        return implode('-', $parts) . '.' . $ext;
    }

    public function pdf(Request $request)
    {
        $loans = $this->getFilteredLoans($request);

        $data = [
            'loans' => $loans,
            'title' => 'Loan Report' . ($request->filled('status') ? ' - ' . ucfirst($request->status) : ''),
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.loan-pdf', $data)->setPaper('a4', 'landscape');

        return $pdf->download($this->getFilename($request, 'pdf'));
    }

    public function excel(Request $request)
    {
        $loans = $this->getFilteredLoans($request);

        return Excel::download(new LoanExport($loans), $this->getFilename($request, 'xlsx'));
    }
}
