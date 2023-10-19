@php
    $field = $getState();
    $phone = '';
	if (is_array($field)) {
		foreach ($field as $key => $value) {
			if(isset($value['@attributes']['PhoneNumber'])){
				$phone .= $value['@attributes']['PhoneNumber'] . '<br>';
			}
		}
	}
@endphp
<div>
    {!! $phone !!}
</div>