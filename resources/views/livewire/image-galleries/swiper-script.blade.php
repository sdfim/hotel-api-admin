@push('scripts')
    <script type="module">
        import Swiper from 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.mjs'

        document.addEventListener("DOMContentLoaded", function() {
            function initializeSwiper() {
                const swiper = new Swiper('.swiper', {
                    // Optional parameters
                    loop: true,
                    speed: 400,
                    spaceBetween: 100,

                    // If we need pagination
                    pagination: {
                        el: '.swiper-pagination',
                    },

                    // Navigation arrows
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },

                    // And if we need scrollbar
                    scrollbar: {
                        el: '.swiper-scrollbar',
                    },
                });

                // Add keyboard event listeners
                document.addEventListener('keydown', function(event) {
                    if (event.key === 'ArrowRight') {
                        swiper.slideNext();
                    } else if (event.key === 'ArrowLeft') {
                        swiper.slidePrev();
                    }
                });
            }

            // Assuming the modal is triggered by an event called 'open-modal'
            document.addEventListener('open-modal', function() {
                setTimeout(() => {
                    const slides = document.querySelectorAll('.swiper-slide');

                    if (slides.length > 0) {
                        initializeSwiper();
                    } else {
                        console.warn('Swiper not initialized: No slides found');
                    }
                }, 300); // Даем время DOM обновиться
            });
        });

        // Example of dispatching the 'open-modal' event for testing purposes
        document.dispatchEvent(new Event('open-modal'));
    </script>
@endpush
