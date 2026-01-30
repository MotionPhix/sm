@component('mail::message')
# You're invited to join {{ $invitation->school->name }}

Hi {{ $invitation->name ?? 'there' }},

You've been invited to join **{{ $invitation->school->name }}** on {{ config('app.name') }}.

@component('mail::button', ['url' => route('invitations.accept', $invitation->token), 'color' => 'success'])
Accept invitation
@endcomponent

This invitation expires on **{{ $invitation->expires_at->format('d M Y') }}**.

If you didn't expect this email, you can safely ignore it.

Thanks,
{{ config('app.name') }}

@slot('subcopy')
If the “Accept invitation” button doesn't work, copy and paste this URL into your browser:
{{ route('invitations.accept', $invitation->token) }}
@endslot
@endcomponent
