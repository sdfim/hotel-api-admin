@extends('channels.layout')
@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>General configuration channels</h2>
            </div>

            <div class="mt-6 mb-6">
                <a class="btn text-violet-500 hover:text-white border-violet-500 hover:bg-violet-600 hover:border-violet-600 focus:bg-violet-600 focus:text-white focus:border-violet-600 focus:ring focus:ring-violet-500/30 active:bg-violet-600 active:border-violet-600"
                    href="{{ route('channels.create') }}"> <i class="bx bx-plus block text-lg"></i></a>
            </div>

        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="relative px-5 py-3 border-2 bg-green-50 text-green-700 border-green-100 rounded mb-3">
            <p>{{ $message }}</p>
        </div>
    @endif
    <div class="col-span-12 xl:col-span-6">
        <div class="card dark:bg-zinc-800 dark:border-zinc-600">
            <div class="card-body pb-0">
                <h6 class="mb-1 text-15 text-gray-700 dark:text-gray-100">General configuration channels table</h6>
            </div>
            <div class="card-body">
                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-500 ">
                        <thead class="text-sm text-gray-700 dark:text-gray-100">
                            <tr class="border border-gray-50 dark:border-zinc-600">
                                <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                    #
                                </th>
                                <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3 border-l border-gray-50 dark:border-zinc-600">
                                    Description
                                </th>
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
                            @foreach ($channels as $channel)
                                <tr class="bg-white border border-gray-50 dark:border-zinc-600 dark:bg-transparent">
                                    <th scope="row"
                                        class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                        {{ $startNumber++ }}
                                    </th>
                                    <td
                                        class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                        {{ $channel->name }}
                                    </td>
                                    <td
                                        class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 font-medium text-gray-900 whitespace-nowrap dark:text-zinc-100">
                                        {{ $channel->description }}
                                    </td>
                                    <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                        {{ $channel->created_at }}
                                    </td>
                                    <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                        {{ $channel->updated_at }}
                                    </td>
                                    <td class="px-6 py-3.5 border-l border-gray-50 dark:border-zinc-600 dark:text-zinc-100">
                                        <form action="{{ route('channels.destroy', $channel->id) }}" method="POST">

                                            <a class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"
                                                href="{{ route('channels.show', $channel->id) }}"><i
                                                    class="bx bx-show block text-lg"></i>
                                            </a>

                                            <a class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"
                                                href="{{ route('channels.edit', $channel->id) }}"><i
                                                    class="mdi mdi-pencil block text-lg"></i><span
                                                    class=""></span></a>

                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"><i
                                                    class="mdi mdi-trash-can block text-lg"></i> </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {!! $channels->appends(request()->query())->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
