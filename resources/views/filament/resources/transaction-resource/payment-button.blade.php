<!-- Midtrans Payment Button and Script -->
<button id="pay-button" class="btn btn-primary">Pay Now</button>

<!-- Midtrans Payment Script -->
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
<script type="text/javascript">
    document.getElementById('pay-button').onclick = function() {
        snap.pay('{{ $snapToken }}', {
            onSuccess: function(result) {
                window.location = '{{ route('user.payment.success', ['transaction_id' => $transaction->id]) }}';
            },
            onPending: function(result) {
                // Handle pending
            },
            onError: function(result) {
                // Handle error
            }
        });
    };
</script>