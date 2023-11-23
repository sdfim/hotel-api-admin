@php
    $field = $getState();
    $phone = '';
	if (is_array($field)) {
		foreach ($field as $key => $value) {
			if(isset($value['PhoneNumber'])){
				$phone .= $value['PhoneNumber'] . '<br>';
			}
		}
	}
@endphp
<div>
    {!! $phone !!}
</div>