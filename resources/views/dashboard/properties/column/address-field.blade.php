@php
    $field = $getState();
    $address = '';
    if(isset($field['AddressLine'])){
		$string = ($field['StreetNmbr'] ?? '') . ' ' . $field['AddressLine'] . 
			' (' . ($field['CityName'] ?? '') . ' ' . ($field['PostalCode'] ?? '') . ' ' . ($field['CountryName'] ?? '') . ')';
		$address = \Modules\API\Tools\StringTool::lineBreak($string);
    }
@endphp
<div>
    {!! $address !!}
</div>