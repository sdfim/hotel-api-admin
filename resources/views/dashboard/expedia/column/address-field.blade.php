@php
    $field = $getState();
    $address = '';
    if(isset($field['line_1'])){
        $address = $field['line_1'];
    }
@endphp
<div>
    {{$address}}
</div>