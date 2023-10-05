@php
    $field = $getState();
    $longitude = '';
    $latitude = '';

    if(isset($field['@attributes'])){
        $longitude = $field['@attributes']['Longitude'];
        $latitude = $field['@attributes']['Latitude'];
    }
@endphp
<div>
    Latitude: {{$latitude}} <br>
    Longitude: {{$longitude}}
</div>