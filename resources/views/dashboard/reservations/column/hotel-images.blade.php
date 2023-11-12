@props([
	'limit' => 5,
])
<div class="fi-ta-image px-3 py-4">
    <div class="flex items-center gap-x-2.5">
        <div class="flex -space-x-2">
            <script type="module">
                GLightbox({
                    selector: '.reservation-glightbox-{{$getRecord()->id}}',
                });
            </script>
            @php
                $record = json_decode($getRecord()->reservation_contains);
                $images = [];
                if(isset($record->hotel_images)){
                    $images = json_decode($record->hotel_images);
                }
				$imagesCount = count($images) > $limit ? $limit : count($images);
            @endphp

            @for ($i = 0; $i < $imagesCount; $i++)
                <a href="{{$images[$i]}}" class="reservation-glightbox-{{$getRecord()->id}}">
                    <img src="{{$images[$i]}}" style="height: 3rem; width: 3rem;"
                         class="max-w-none object-cover object-center rounded-full ring-white dark:ring-gray-900 ring-2 img-fluid"
                         alt="work-thumbnail">
                </a>
            @endfor
        </div>
    </div>
</div>
