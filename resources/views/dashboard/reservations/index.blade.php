@extends('layouts.master')
@section('title')
    {{ __('Reservation') }}
@endsection
@section('css')
    <!-- DataTables -->
    <link href="{{ URL::asset('build/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')  }}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('build/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')  }}" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="{{ URL::asset('build/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')  }}" rel="stylesheet"
        type="text/css" />

    <style>
        .form-actions{
            display: flex;
            justify-content: space-between;
        }

        .hasTooltip span {
            display: none;
            color: #000;
            text-decoration: none;
            padding: 3px;
        }

        .hasTooltip:hover span {
            display: block;
            position: absolute;
            background-color: #FFF;
            border: 1px solid #CCC;
            margin: 40px 0px;
        }
    </style>
@endsection
@section('content')

    <!-- -->
    <x-page-title title="Reservations" pagetitle="index" />

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    <table id="datatable" class="table w-full pt-4 text-gray-700 dark:text-zinc-100">
                        <thead>
                            <tr>
                                <th class="p-4 pr-8 border rtl:border-l-0 border-y-2 border-gray-50 dark:border-zinc-600">ID</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0">Created Date</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0">Offload date</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0">Date of Travel</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0" data-orderable="false">Passenger Surname</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0" data-orderable="false">Contains</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0" data-orderable="false">Channel</th>
                                <th class="p-4 pr-8 border border-y-2 border-gray-50 dark:border-zinc-600 border-l-0">Total Cost</th>
                                <th class="p-4 pr-8 border rtl:border-l border-y-2 border-gray-50 dark:border-zinc-600 border-l-0" data-orderable="false"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td class="p-4 pr-8 border rtl:border-l-0 border-t-0 border-gray-50 dark:border-zinc-600">{{$reservation->id}}</td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">{{$reservation->created_at}}</td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">{{$reservation->date_offload ?? 'N/A'}}</td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">{{$reservation->date_travel}}</td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">{{$reservation->passenger_surname}}</td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">
                                        <div class="icon-demo-content hasTooltip">
                                            @if($reservation->contains->name == 'Flight')
                                                <i class="mdi mdi-airplane"></i>
                                                <span>TEXT INFO Flight</span>
                                            @elseif($reservation->contains->name == 'Hotel')
                                                <i class="mdi mdi-home"></i>
                                                <span>TEXT INFO Hotel</span>
                                            @elseif($reservation->contains->name == 'Transfer')
                                                <i class="mdi mdi-transit-transfer"></i>
                                                <span>TEXT INFO Transfer</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">{{$reservation->channel->name}}</td>
                                    <td class="p-4 pr-8 border border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">{{$reservation->total_cost}}</td>
                                    <td class="p-4 pr-8 border rtl:border-l border-t-0 border-l-0 border-gray-50 dark:border-zinc-600">
                                    <form class="form-actions" action="{{ route('reservations.cancel', $reservation->id) }}" method="GET">
                                            <a class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"
                                                href="{{ route('reservations.show', $reservation->id) }}"><i
                                                    class="bx bx-show block text-lg"></i>
                                            </a>
                                            @csrf
                                            <button type="submit"
                                                class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"><i
                                                    class="mdi mdi-cancel block text-lg"></i> </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <livewire:reservations-table/>
@endsection
@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

    <!-- Required datatable js -->
    <script src="{{ URL::asset('build/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Buttons examples -->
    <script src="{{ URL::asset('build/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/jszip/jszip.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>

    <!-- Responsive examples -->
    <script src="{{ URL::asset('build/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ URL::asset('build/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>

    <!-- Datatable init js -->
    <script src="{{ URL::asset('build/js/pages/datatables.init.js') }}"></script>
@endsection
