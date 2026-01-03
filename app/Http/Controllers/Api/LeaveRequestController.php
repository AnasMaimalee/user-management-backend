<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\LeaveStatusMail;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeaveExport;
class LeaveRequestController extends Controller
{

    public function index(Request $request)
    {
        abort_unless($request->user()->can('view leaves'), 403);

        $leaveRequests = LeaveRequest::with([
            'user',
            'user.employee',
            'user.employee.department',
            'user.employee.rank',
            'user.employee.branch'
        ])->latest()->get();

        return response()->json($leaveRequests);
    }

    public function myLeaves()
    {
        return LeaveRequest::where('user_id', Auth::id())
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|min:5|max:255',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'resume_date' => 'required|date|after_or_equal:end_date',
        ]);

        $leave = LeaveRequest::create([
            'reason' => $request->reason,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'resume_date' => $request->resume_date,
            'user_id' => Auth()->id()
        ]);

        return response()->json([
            'message' => 'Leave Request submitted successfully.',
            'leave_request' => $leave
        ]);
    }

    public function update(Request $request, LeaveRequest $leave)
    {
        // Only allow update if status is pending
        if ($leave->status !== 'pending') {
            return response()->json([
                'message' => 'This leave request has already been processed.'
            ], 422);
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_note' => 'nullable|string|max:255',
        ]);

        if ($request->status === 'rejected') {
            $request->validate([
                'admin_note' => 'required|string|min:10|max:255',
            ]);
        }

        $leave->update([
            'status'       => $validated['status'],
            'admin_note'   => $validated['admin_note'] ?? null,
            'reviewed_by'  => auth()->id(),
            'reviewed_at'  => now(),
        ]);

        // SAFE EMAIL SEND - This prevents the crash
        $employeeEmail = $leave->user?->employee?->email;

        if ($employeeEmail) {
            try {
                Mail::to($employeeEmail)->send(new LeaveStatusMail($leave));
            } catch (\Exception $e) {
                \Log::error('Failed to send leave status email: ' . $e->getMessage());
                // Don't fail the whole request just because email failed
            }
        } else {
            \Log::warning('No employee email found for leave request ID: ' . $leave->id);
        }

        // Reload with proper relations for response
        $leave->load(['user.employee']);

        return response()->json([
            'message'        => 'Leave request ' . $validated['status'] . ' successfully.',
            'leave_request'  => $leave
        ]);
    }

    // Add these methods to your LeaveRequestController
    public function exportPdf(Request $request, $type)
    {
        abort_unless($request->user()->can('view leaves'), 403);

        $query = LeaveRequest::with([
            'user',
            'user.employee',
            'user.employee.department',
            'user.employee.rank',
            'user.employee.branch'
        ]);

        if ($type === 'pending') {
            $query->where('status', 'pending');
        } elseif ($type === 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        // Filters - CORRECT CHAIN: user → employee → department
        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }
        if ($request->filled('employee')) {
            $query->whereHas('user.employee', function ($q) use ($request) {
                $q->where('id', $request->employee);
            });
        }
        if ($request->filled('department')) {
            $query->whereHas('user.employee.department', function ($q) use ($request) {
                $q->where('id', $request->department);
            });
        }

        $leaves = $query->latest()->get();

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.leaves', [
            'leaves' => $leaves,
            'title' => $type === 'pending' ? 'Pending Leave Requests' : 'Leave History',
            'date' => now()->format('F j, Y'),
        ]);

        return $pdf->download('leave_requests_' . $type . '_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request, $type)
    {
        abort_unless($request->user()->can('view leaves'), 403);

        $query = LeaveRequest::with([
            'user',
            'user.employee',
            'user.employee.department',
            'user.employee.rank',
            'user.employee.branch'
        ]);

        if ($type === 'pending') {
            $query->where('status', 'pending');
        } elseif ($type === 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
        }

        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }
        if ($request->filled('employee')) {
            $query->whereHas('user.employee', function ($q) use ($request) {
                $q->where('id', $request->employee);
            });
        }
        if ($request->filled('department')) {
            $query->whereHas('user.employee.department', function ($q) use ($request) {
                $q->where('id', $request->department);
            });
        }

        $leaves = $query->latest()->get();

        $filename = 'leave_requests_' . $type . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new LeaveExport($leaves, $type), $filename);
    }

    /**
     * Export employee's own pending leaves to PDF
     */
    public function exportMyPdf(Request $request, $type)
    {
        $user = auth()->user();

        $query = LeaveRequest::with([
            'user.employee',
            'user.employee.department',
            'user.employee.rank',
            'user.employee.branch'
        ])->where('user_id', $user->id);

        if ($type === 'pending') {
            $query->where('status', 'pending');
            $title = 'My Pending Leave Requests';
        } elseif ($type === 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            $title = 'My Leave History';
        } else {
            abort(404);
        }

        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $leaves = $query->latest()->get();

        if ($leaves->isEmpty()) {
            return response()->json(['message' => 'No records found to export'], 404);
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('exports.leaves', [
            'leaves' => $leaves,
            'title'  => $title . ' - ' . $user->employee?->first_name . ' ' . $user->employee?->last_name,
            'date'   => now()->format('F j, Y'),
        ]);

        return $pdf->download('my_leave_requests_' . $type . '_' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Export employee's own leaves to Excel
     */
    public function exportMyExcel(Request $request, $type)
    {
        $user = auth()->user();

        $query = LeaveRequest::with([
            'user.employee',
            'user.employee.department',
            'user.employee.rank',
            'user.employee.branch'
        ])->where('user_id', $user->id);

        if ($type === 'pending') {
            $query->where('status', 'pending');
        } elseif ($type === 'history') {
            $query->whereIn('status', ['approved', 'rejected']);
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
        } else {
            abort(404);
        }

        if ($request->filled('month')) {
            $query->whereMonth('start_date', $request->month);
        }
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $leaves = $query->latest()->get();

        if ($leaves->isEmpty()) {
            return response()->json(['message' => 'No records found to export'], 404);
        }

        $filename = 'my_leave_requests_' . $type . '_' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new LeaveExport($leaves, $type), $filename);
    }
}
