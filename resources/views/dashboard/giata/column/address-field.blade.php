@php
    $field = $getState();
    $address = '';
    if(isset($field['AddressLine'])){
        $address = $field['AddressLine'];
    }
@endphp
<div>
    {{$address}}
</div>