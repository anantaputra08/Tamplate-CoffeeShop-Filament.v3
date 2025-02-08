<div>
    <h2 class="text-lg font-bold">Transaction Details</h2>
    <div class="p-4">
        <p><strong>Order ID:</strong> {{ $record->order_id }}</p>
        <p><strong>Cashier:</strong> {{ $record->user->name }}</p>
        <p><strong>Status:</strong> {{ $record->status }}</p>
        <p><strong>Payment Type:</strong> {{ $record->payment_type }}</p>
        <p><strong>Gross Amount:</strong> Rp. {{ number_format($record->gross_amount, 0, ',', '.') }}</p>
        <!-- Add more fields as needed -->
    </div>
    <div class="flex justify-end space-x-2 mt-4">
        <!-- Close Button -->
        <x-filament::button color="secondary" onclick="window.dispatchEvent(new CustomEvent('close-modal'))">
            Close
        </x-filament::button>
        
        <!-- Submit Button -->
        <x-filament::button color="success" wire:click="submit">
            Mark as Completed
        </x-filament::button>
    </div>
</div>