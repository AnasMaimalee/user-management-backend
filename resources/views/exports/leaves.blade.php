<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        /* PDF-friendly font and base styles */
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }

        h1 {
            font-size: 18px;
            color: #1e40af;
            text-align: center;
            margin-bottom: 10px;
        }

        .generated-date {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; /* Critical: forces columns to respect width */
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #999;
            padding: 8px 6px;
            text-align: left;
            vertical-align: top;
            word-wrap: break-word; /* Wrap long text */
        }

        th {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
            font-size: 10px;
            text-align: center;
        }

        /* Column widths - adjust if needed */
        th:nth-child(1), td:nth-child(1) { width: 5%; }   /* # */
        th:nth-child(2), td:nth-child(2) { width: 15%; }  /* Employee */
        th:nth-child(3), td:nth-child(3) { width: 18%; }  /* Email */
        th:nth-child(4), td:nth-child(4) { width: 12%; }  /* Department */
        th:nth-child(5), td:nth-child(5) { width: 10%; }  /* Branch */
        th:nth-child(6), td:nth-child(6) { width: 10%; }  /* Rank */
        th:nth-child(7), td:nth-child(7) { width: 9%; }   /* Start Date */
        th:nth-child(8), td:nth-child(8) { width: 9%; }   /* End Date */
        th:nth-child(9), td:nth-child(9) { width: 9%; }   /* Resume Date */
        th:nth-child(10), td:nth-child(10) { width: 15%; } /* Reason */
        th:nth-child(11), td:nth-child(11) { width: 8%; }  /* Status */
        th:nth-child(12), td:nth-child(12) { width: 10%; } /* Applied On */

        tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .status-approved { color: #16a34a; font-weight: bold; }
        .status-rejected { color: #dc2626; font-weight: bold; }
        .status-pending { color: #ea580c; font-weight: bold; }

        /* Page break handling for long tables */
        table { page-break-inside: auto; }
        tr    { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
    </style>
</head>
<body>
<h1>{{ $title }}</h1>
<p class="generated-date">Generated on: {{ $date }}</p>

@if($leaves->isEmpty())
    <p style="text-align: center; font-size: 14px; color: #666;">No leave records found for the selected criteria.</p>
@else
    <table>
        <thead>
        <tr>
            <th>#</th>
            <th>Employee</th>
            <th>Email</th>
            <th>Department</th>
            <th>Branch</th>
            <th>Rank</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Resume Date</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Applied On</th>
        </tr>
        </thead>
        <tbody>
        @foreach($leaves as $index => $leave)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>

                <td>
                    @if($leave->user?->employee)
                        {{ $leave->user->employee->first_name }} {{ $leave->user->employee->last_name }}
                    @else
                        <em>Unknown Employee</em>
                    @endif
                </td>

                <td>{{ $leave->user?->employee?->email ?? 'N/A' }}</td>

                <td>{{ $leave->user?->employee?->department?->name ?? 'N/A' }}</td>

                <td>{{ $leave->user?->employee?->branch?->name ?? 'N/A' }}</td>

                <td>{{ $leave->user?->employee?->rank?->name ?? 'N/A' }}</td>

                <td>{{ \Carbon\Carbon::parse($leave->start_date)->format('M d, Y') }}</td>

                <td>{{ \Carbon\Carbon::parse($leave->end_date)->format('M d, Y') }}</td>

                <td>
                    {{ $leave->resume_date
                        ? \Carbon\Carbon::parse($leave->resume_date)->format('M d, Y')
                        : '—'
                    }}
                </td>

                <td>{{ $leave->reason ?: '—' }}</td>

                <td>
                            <span class="status-{{ strtolower($leave->status) }}">
                                {{ ucfirst($leave->status) }}
                            </span>
                </td>

                <td>{{ \Carbon\Carbon::parse($leave->created_at)->format('M d, Y') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
</body>
</html>
