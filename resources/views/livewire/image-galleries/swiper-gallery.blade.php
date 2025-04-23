<div class="swiper">
    <div class="swiper-wrapper">
        @php
            use Illuminate\Support\Facades\Storage;
            $validAlts = ['Header image', 'Thumbnail image', 'Gallery image'];
        @endphp
        @foreach($images as $image)
            <div class="swiper-slide">
                <img src="{{ $image['full_url'] }}" alt="{{ $image['alt'] }}"
                     style="width: 100%; height: 400px; object-fit: cover;">
            </div>
        @endforeach
    </div>

    <!-- Navigation buttons -->
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>

    {{--    <!-- Pagination -->--}}
    <div class="swiper-pagination"></div>

    {{--    <!-- If we need scrollbar -->--}}
    <div class="swiper-scrollbar"></div>
</div>


