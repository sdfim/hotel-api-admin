@if($getState())
    @php
        $field = $getState();
        $i = 0;
        $counter = count($field)-1;
    @endphp
    <div style="display: flex; flex-direction: column">
    @foreach($field as $giata)
        @if($i == $counter)
        <a href="{{route('giata.show', $giata->giata_id)}}" target="_blank">{{$giata->giata_id}}</a>
        @else
        <a href="{{route('giata.show', $giata->giata_id)}}" target="_blank">{{$giata->giata_id}}, </a>
        @endif
        @php
            $i++;
        @endphp
    @endforeach
    </div>
@endif