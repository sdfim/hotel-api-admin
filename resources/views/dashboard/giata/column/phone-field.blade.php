@php
    $field = $getState();
    $phone = '';
    if(isset($field['@attributes'])){
        $phone = $field['@attributes']['PhoneNumber'];
    }
@endphp
<div>
    {{$phone}}
</div>