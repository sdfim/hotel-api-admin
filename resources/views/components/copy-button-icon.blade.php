@props(['value'])
<div class="copy-clipboard-btn" data-value="{{ $value }}" alt="Copy to Clipboard" title="Copy to Clipboard">
    <i class="fa fa-clipboard"></i>
</div>

@once
    @push('livewire-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('.copy-clipboard-btn').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        const value = this.getAttribute('data-value');
                        if (value) {
                            navigator.clipboard.writeText(value).then(function () {
                                showTooltip(btn, 'Copied!');
                            }, function (err) {
                                console.error('Error on copy: ', err);
                            });
                        }
                    });
                });

                function showTooltip(element, message) {
                    // Crea el tooltip
                    const tooltip = document.createElement('div');
                    tooltip.textContent = message;
                    tooltip.className = 'copy-tooltip'
                    document.body.appendChild(tooltip);

                    // Usa Popper.js para posicionar el tooltip
                    Popper.createPopper(element, tooltip, {
                        placement: 'top',
                    });

                    // Oculta el tooltip después de 2 segundos
                    setTimeout(() => {
                        tooltip.remove();
                    }, 2000);
                }
            });
        </script>
    @endpush
@endonce
