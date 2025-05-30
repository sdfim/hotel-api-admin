@php
    $field = $getState();
    $address = '';
    if(isset($field['AddressLine'])){
		$string = ($field['StreetNmbr'] ?? '') . ' ' . $field['AddressLine'] .
			' (' . ($field['CityName'] ?? '') . ' ' . ($field['PostalCode'] ?? '') . ' ' . ($field['CountryName'] ?? '') . ')';
		$address = \Modules\API\Tools\StringTool::lineBreak($string);
    }
    $address = '<span class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight fi-ta-text-item-label text-sm leading-6 text-gray-950">' . $address . '</span>';
@endphp
<div>
    {!! $address !!}
</div>
