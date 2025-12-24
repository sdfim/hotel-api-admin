<style>
    .swiper-slide {
        position: relative;
    }

    .image-info {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 10px;
        background: rgba(0, 0, 0, 0.5);
        color: #fff;
        z-index: 10;
    }
</style>

<div class="swiper">
    <div class="swiper-wrapper">
        @php
            use Illuminate\Support\Facades\Storage;
            $validAlts = ['Header image', 'Thumbnail image', 'Gallery image'];
        @endphp
        @foreach($images as $image)
            @php
                $tags = explode(';', $image['tag']);
                $size = intval(str_replace('px', '', $tags[0] ?? 0));
                if ($size && ($size < 500 || in_array('icon', $tags))) {
                    continue;
                }
                $source = $image['source'];
                $source = $source == 'own' ? 'internal' : $source;
            @endphp
            <div class="swiper-slide">
                <img src="{{ $image['full_url'] }}" alt="{{ $image['alt'] }}"
                     style="width: 100%; height: 500px; object-fit: cover;">
                <div class="image-info">
                    <p>Alt: {{ $image['alt'] }}</p>
                    <p>Tag: {{ $image['tag'] }}</p>
                    <p>Source: {{ $source }}</p>

                </div>
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
