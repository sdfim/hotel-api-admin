@php
    $field = $getState();
    $address = '';
    if(isset($field['line_1'])){
        $address = \Modules\API\Tools\StringTool::lineBreak($field['line_1']);
    }
@endphp
<div>
    {!! $address !!}
</div>