<div class="slider-container"
     x-data="{
       currentSlide: 0,
       slidesCount: {{ count($items) }},
       startX: 0,
       moveX: 0,
       next() {
           this.currentSlide = (this.currentSlide + 1) % this.slidesCount;
       },
       prev() {
           this.currentSlide = (this.currentSlide - 1 + this.slidesCount) % this.slidesCount;
       },
       handleTouchStart(e) {
           e.stopPropagation();
           this.startX = e.touches[0].clientX;
       },
       handleTouchMove(e) {
           e.stopPropagation();
           if (!this.startX) return;
           this.moveX = e.touches[0].clientX;
       },
       handleTouchEnd(e) {
           e.stopPropagation();
           if (!this.startX || !this.moveX) return;
           const diff = this.startX - this.moveX;
           if (Math.abs(diff) > 50) {
               if (diff > 0) {
                   this.next();
               } else {
                   this.prev();
               }
           }
           this.startX = 0;
           this.moveX = 0;
       }
     }">
    <div class="slider"
         @touchstart.stop="handleTouchStart"
         @touchmove.stop="handleTouchMove"
         @touchend.stop="handleTouchEnd"
         @click.stop>
        <div class="slides" :style="`transform: translateX(-${currentSlide * 100}%)`">
            @foreach ($items as $index => $image)
                <div class="slide">
                    <img src="{{ $image }}" alt="Image {{ $index + 1 }}"
                         class="w-full h-auto rounded-lg shadow-md">
                </div>
            @endforeach
        </div>

        <button @click.stop.prevent="prev" class="nav-arrow left" aria-label="Previous slide">&lt;
        </button>
        <button @click.stop.prevent="next" class="nav-arrow right" aria-label="Next slide">&gt;
        </button>
    </div>
</div>
