@php
    $field = $getState();
    $address = '';
    if(isset($field['AddressLine'])){
		$address = \Modules\API\Tools\StringTool::lineBreak($field['AddressLine']);
    }
@endphp
<div>
    {!! $address !!}
</div>