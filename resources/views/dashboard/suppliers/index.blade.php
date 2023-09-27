@extends('dashboard.suppliers.layout')
@section('css')
    <!-- DataTables -->
    <link href="{{ URL::asset('build/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css')  }}" rel="stylesheet"
          type="text/css"/>
    <link href="{{ URL::asset('build/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css')  }}"
          rel="stylesheet" type="text/css"/>

    <!-- Responsive datatable examples -->
    <link href="{{ URL::asset('build/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css')  }}"
          rel="stylesheet"
          type="text/css"/>

    <style>
        .form-actions {
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
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Suppliers</h2>
            </div>

            <div class="mt-6 mb-6">
                <a class="btn text-violet-500 hover:text-white border-violet-500 hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white focus:border-violet-600 focus:ring focus:ring-violet-500/30 active:bg-violet-600 active:border-violet-600"
                   href="{{ route('suppliers.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
            </div>

        </div>
    </div>
    @if ($message = Session::get('success'))
        <div
            class="relative flex items-center px-5 py-2 border-2 text-green-500 border-green-500 rounded alert-dismissible">
            <p>{{ $message }}</p>
            <button class="alert-close ltr:ml-auto rtl:mr-auto text-green-400 text-lg"><i class="mdi mdi-close"></i>
            </button>
        </div>
    @endif
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0 ">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Suppliers table</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto overflow-y-auto">
                    <table id="datatable" class="pt-4 w-full text-sm text-left text-gray-500 ">
                        <thead class="text-sm text-gray-700 dark:text-gray-100">
                        <tr class="border border-gray-50 dark:border-zinc-600">
                            <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                #
                            </th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Name</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Description</th>
                            <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                Create
                            </th>
                            <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                Update
                            </th>
                            <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                Action
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($suppliers as $item)
                            <tr class="bg-white border border-gray-50 dark:border-zinc-600 dark:bg-transparent">
                                <th scope="row"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $startNumber++ }}
                                </th>
                                <td data-field="name"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->name }}
                                </td>
                                <td data-field="description"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->description }}
                                </td>
                                <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                    {{ $item->created_at }}
                                </td>
                                <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                    {{ $item->updated_at }}
                                </td>
                                <td
                                    class="w-40 px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                    <form class="w-40" action="{{ route('suppliers.destroy', $item->id) }}"
                                          method="POST">
                                        <x-button-icon route="{{ route('suppliers.show', $item->id) }}"
                                                       iconClass="bx bx-show"/>
                                        <x-button-icon route="{{ route('suppliers.edit', $item->id) }}"
                                                       iconClass="mdi mdi-pencil"/>

                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100">
                                            <i
                                                class="mdi mdi-trash-can block text-lg"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    {!! $suppliers->appends(request()->query())->links() !!}
                </div>
            </div>
        </div>
    </div>
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
