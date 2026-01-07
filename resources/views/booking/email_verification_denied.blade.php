@extends('booking.vidanta_landing_layout')

@section('title', 'Quote Declined - ' . env('APP_NAME'))

@section('content')
    <h1 class="font-playfair italic text-5xl text-vidantaBlack mb-8">Quote Declined</h1>

    <div class="space-y-6 text-vidantaBlack/80 leading-relaxed font-light text-lg">
        <p>
            You have successfully declined the proposed quote.
        </p>
        <p>
            Our concierge team has been notified. We will reach out to you shortly to discuss any changes or provide an
            alternative proposal that better fits your needs.
        </p>
    </div>

    <div class="mt-12 flex justify-center">
        <div class="w-12 h-[1px] bg-vidantaBronze"></div>
    </div>
@endsection