<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request {{ ucfirst($leave->status) }}</title>
    <!-- Tailwind CSS via CDN (works great for emails) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 antialiased">
<div class="max-w-2xl mx-auto my-12">
    <!-- Main Card -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Header -->
        <div class="{{ $leave->status === 'approved' ? 'bg-green-600' : 'bg-red-600' }} text-white py-10 text-center">
            <h1 class="text-3xl font-bold">Leave Request {{ ucfirst($leave->status) }}</h1>
            <p class="mt-2 text-lg opacity-90">Your request has been reviewed</p>
        </div>

        <!-- Content -->
        <div class="p-8 md:p-10">
            <p class="text-lg text-gray-800 mb-6">
                Hello <strong class="font-semibold">{{ $employee->first_name ?? $employee->name }}</strong>,
            </p>

            <p class="text-lg text-gray-700 mb-8">
                Your leave request has been
                <span class="inline-block px-4 py-2 rounded-full font-bold text-white {{ $leave->status === 'approved' ? 'bg-green-600' : 'bg-red-600' }}">
                        {{ strtoupper($leave->status) }}
                    </span>
            </p>

            <!-- Details Card -->
            <div class="bg-gray-50 rounded-lg p-6 border-l-4 {{ $leave->status === 'approved' ? 'border-green-500' : 'border-red-500' }} mb-8">
                <h3 class="font-semibold text-gray-900 mb-4 text-lg">Leave Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-700">
                    <div>
                        <p class="text-sm text-gray-500">Start Date</p>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($leave->start_date)->format('l, F j, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">End Date</p>
                        <p class="font-medium">{{ \Carbon\Carbon::parse($leave->end_date)->format('l, F j, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Resume Date</p>
                        <p class="font-medium">
                            {{ $leave->resume_date
                                ? \Carbon\Carbon::parse($leave->resume_date)->format('l, F j, Y')
                                : 'Next working day' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Reason</p>
                        <p class="font-medium">{{ $leave->reason }}</p>
                    </div>
                </div>
            </div>

            <!-- Admin Note -->
            @if($leave->admin_note)
                <div class="bg-amber-50 border-l-4 border-amber-400 p-6 rounded-lg mb-8">
                    <p class="font-semibold text-amber-900 mb-2">Message from HR</p>
                    <p class="text-amber-800 italic leading-relaxed">{{ $leave->admin_note }}</p>
                </div>
            @endif

            <!-- Message -->
            <p class="text-gray-700 mb-8 leading-relaxed">
                @if($leave->status === 'approved')
                    Enjoy your well-deserved time off! We look forward to having you back refreshed.
                @else
                    If you have any questions regarding this decision, please feel free to contact HR.
                @endif
            </p>

            <!-- Login Button -->
            <div class="text-center">
                <a href="{{ url('/login') }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-lg text-lg transition shadow-md">
                    Log in to HR Portal
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-50 px-10 py-8 text-center text-gray-600">
            <p class="font-medium">Best regards,</p>
            <p class="font-bold text-gray-800 mt-1">Maimalee HR Team</p>
            <p class="text-sm mt-4">This is an automated message — please do not reply directly.</p>
        </div>
    </div>
</div>
</body>
</html>
