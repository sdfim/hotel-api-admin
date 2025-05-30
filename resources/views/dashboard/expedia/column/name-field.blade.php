@php
    $name = \Modules\API\Tools\StringTool::lineBreak($getState());
    $name = '<span class="text-slate-900 dark:text-white mt-5 text-base font-medium tracking-tight fi-ta-text-item-label text-sm leading-6 text-gray-950">' . $name . '</span>';
@endphp
<div>
    {!! $name !!}
</div>
