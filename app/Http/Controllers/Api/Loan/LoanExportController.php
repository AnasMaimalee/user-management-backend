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
    /**
     * Shared method to get filtered loans
     * Used by both admin and employee exports
     */
    private function getFilteredLoans(Request $request, $employeeId = null)
    {
        $query = Loan::with(['employee.department'])->latest('updated_at');

        // Restrict to specific employee if provided (for "My Loans")
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        // Admin-only: Exclude pending by default unless status specified
        if (!$employeeId && !$request->filled('status')) {
            $query->whereNot('status', 'pending'); // History view default
        }

        // If status is explicitly sent (e.g. from Pending table), apply it
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Year filter
        if ($request->filled('year')) {
            $year = $request->year;
            $query->where(function ($q) use ($year) {
                $q->whereYear('created_at', $year)
                    ->orWhereYear('approved_at', $year)
                    ->orWhereYear('updated_at', $year);
            });
        }

        // Month filter (requires year)
        if ($request->filled('month') && $request->filled('year')) {
            $month = $request->month;
            $query->where(function ($q) use ($month) {
                $q->whereMonth('created_at', $month)
                    ->orWhereMonth('approved_at', $month)
                    ->orWhereMonth('updated_at', $month);
            });
        }

        // Admin filters (not available to employees)
        if (!$employeeId) {
            if ($request->filled('employee_id')) {
                $query->where('employee_id', $request->employee_id);
            }

            if ($request->filled('department_id')) {
                $query->whereHas('employee', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        }

        $loans = $query->get();

        if ($loans->isEmpty()) {
            abort(404, 'No loan records found for the selected filters');
        }

        // Add paid_amount accessor
        $loans->transform(fn($loan) => $loan->append('paid_amount'));

        return $loans;
    }

    /**
     * Employee: Export MY loans as PDF
     */
    public function myPdf(Request $request)
    {
        $loans = $this->getFilteredLoans($request, auth()->user()->employee_id);

        $data = [
            'loans'        => $loans,
            'title'        => 'My Loan Report',
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.loan-pdf', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'My-Loans-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Employee: Export MY loans as Excel
     */
    public function myExcel(Request $request)
    {
        $loans = $this->getFilteredLoans($request, auth()->user()->employee_id);

        $filename = 'My-Loans-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new LoanExport($loans), $filename);
    }

    /**
     * Admin: Export all/filtered loans as PDF
     */
    public function pdf(Request $request)
    {
        $request->validate([
            'year'          => 'nullable|integer|min:2000|max:2035',
            'month'         => 'nullable|integer|min:1|max:12',
            'employee_id'   => 'nullable|uuid|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'status'        => 'nullable|in:pending,approved,rejected,completed',
        ]);

        $loans = $this->getFilteredLoans($request);

        $title = 'Loan Report';
        if ($request->filled('status')) {
            $title .= ' - ' . ucfirst($request->status);
        }
        if ($request->filled('year')) {
            $title .= ' - ' . $request->year;
        }

        $data = [
            'loans'        => $loans,
            'title'        => $title,
            'generated_at' => now()->format('d F Y, H:i'),
        ];

        $pdf = Pdf::loadView('exports.loan-pdf', $data)
            ->setPaper('a4', 'landscape');

        $filename = 'Loan-Report-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Admin: Export all/filtered loans as Excel
     */
    public function excel(Request $request)
    {
        $request->validate([
            'year'          => 'nullable|integer|min:2000|max:2035',
            'month'         => 'nullable|integer|min:1|max:12',
            'employee_id'   => 'nullable|uuid|exists:employees,id',
            'department_id' => 'nullable|exists:departments,id',
            'status'        => 'nullable|in:pending,approved,rejected,completed',
        ]);

        $loans = $this->getFilteredLoans($request);

        $filename = 'Loan-Report-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new LoanExport($loans), $filename);
    }
}
