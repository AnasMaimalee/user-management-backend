<!-- resources/views/emails/employee-invitation.blade.php -->

<x-mail::message>
    # Welcome to Maimalee HR Portal

    Dear {{ $employee->first_name }} {{ $employee->last_name }},

    You've been added to the Maimalee HR Management System.

    To access your profile, view payslips, request leave, and more, please set up your account:

    <x-mail::button :url="$url">
        Set Your Password
    </x-mail::button>

    This link will expire in 7 days.

    **Login Email:** {{ $employee->email }}

    If you have any questions, contact HR.

    Thank you!
    Maimalee HR Team
</x-mail::message>
