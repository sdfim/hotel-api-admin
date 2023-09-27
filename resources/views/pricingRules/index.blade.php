@extends('pricingRules.layout')
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Pricing Rules</h2>
            </div>

            <div class="mt-6 mb-6">
                <a class="btn text-violet-500 hover:text-white border-violet-500 hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white focus:border-violet-600 focus:ring focus:ring-violet-500/30 active:bg-violet-600 active:border-violet-600"
                   href="{{ route('pricing_rules.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
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
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">Pricing Rules table</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto overflow-y-auto">
                    <table class="w-full text-sm text-left text-gray-500 ">
                        <thead class="text-sm text-gray-700 dark:text-gray-100">
                        <tr class="border border-gray-50 dark:border-zinc-600">
                            <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                #
                            </th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Name</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Property</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Destination</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Travel Date</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Days until Travel</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Nights</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Supplier</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Rate Code</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Room type</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Total Guests</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Room Guests</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Number of Rooms</th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Meal Plan / Board Basis
                            </th>
                            <th class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">Rating</th>
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
                        @foreach ($pricingRules as $item)
                            <tr class="bg-white border border-gray-50 dark:border-zinc-600 dark:bg-transparent">
                                <th scope="row"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $startNumber++ }}
                                </th>
                                <td data-field="name"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->name }}
                                </td>
                                <td data-field="property"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->property }}
                                </td>
                                <td data-field="destination"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->destination }}
                                </td>
                                <td data-field="travel_date"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->travel_date }}
                                </td>
                                <td data-field="days"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->days }}
                                </td>
                                <td data-field="nights"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->nights }}
                                </td>
                                <td data-field="supplier_id"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->suppliers->name }}
                                </td>
                                <td data-field="rate_code"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->rate_code }}
                                </td>
                                <td data-field="room_type"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->room_type }}
                                </td>
                                <td data-field="total_guests"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->total_guests }}
                                </td>
                                <td data-field="room_guests"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->room_guests }}
                                </td>
                                <td data-field="number_rooms"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->number_rooms }}
                                </td>
                                <td data-field="meal_plan"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->meal_plan }}
                                </td>
                                <td data-field="rating"
                                    class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                    {{ $item->rating }}
                                </td>
                                <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                    {{ $item->created_at }}
                                </td>
                                <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                    {{ $item->updated_at }}
                                </td>
                                <td
                                    class="w-40 px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                    <form class="w-40" action="{{ route('pricing_rules.destroy', $item->id) }}"
                                          method="POST">
                                        <x-button-icon route="{{ route('pricing_rules.show', $item->id) }}"
                                                       iconClass="bx bx-show"/>
                                        <x-button-icon route="{{ route('pricing_rules.edit', $item->id) }}"
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
                    {!! $pricingRules->appends(request()->query())->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
