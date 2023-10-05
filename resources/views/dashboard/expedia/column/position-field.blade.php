@php
    $field = $getState();
    $longitude = '';
    $latitude = '';

    if(isset($field['coordinates'])){
        $longitude = $field['coordinates']['longitude'];
        $latitude = $field['coordinates']['latitude'];
    }
@endphp
<div>
    Latitude: {{$latitude}} <br>
    Longitude: {{$longitude}}
</div>