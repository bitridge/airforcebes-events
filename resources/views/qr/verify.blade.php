<x-guest-layout>
    <div class="max-w-lg mx-auto mt-10 bg-white rounded-xl shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">QR Verification</h1>
        <p class="text-sm text-gray-600 mb-6">This page confirms the QR code is valid.</p>

        <div class="space-y-3">
            <div>
                <div class="text-xs uppercase text-gray-500">Registration Code</div>
                <div class="font-mono text-lg font-semibold">{{ $registration->registration_code }}</div>
            </div>
            <div>
                <div class="text-xs uppercase text-gray-500">Attendee</div>
                <div class="font-medium">{{ $registration->user->name }}</div>
            </div>
            <div>
                <div class="text-xs uppercase text-gray-500">Event</div>
                <div class="font-medium">{{ $registration->event->title }}</div>
            </div>
            <div>
                <div class="text-xs uppercase text-gray-500">Status</div>
                @if($registration->isCheckedIn())
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Checked In</span>
                @else
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Not Checked In</span>
                @endif
            </div>
        </div>

        <div class="mt-6">
            <a href="{{ route('events.show', $registration->event->slug) }}" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">View Event</a>
        </div>
    </div>
</x-guest-layout>
