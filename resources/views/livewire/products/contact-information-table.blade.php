<div>
    {{ $this->table }}
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        window.addEventListener('copy-to-clipboard', async function (event) {
            const emails = event.detail[0].emails;
            try {
                await navigator.clipboard.writeText(emails);
            } catch (err) {
                console.error('Failed to copy: ', err);
            }
        });
    });
</script>

