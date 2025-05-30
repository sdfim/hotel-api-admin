@extends('layouts.master')
@section('title')
    {{ __('FAQs') }}
@endsection
@section('content')
    <x-page-title title="FAQs" pagetitle="Components"/>

    <div class="grid grid-cols-12 gap-5 mb-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body mb-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12">
                        <div class="col-span-12 lg:col-span-4 lg:col-start-5">
                            <div class="text-center">
                                <h5 class="text-gray-700 dark:text-gray-100">Can't find what you are looking for?</h5>
                                <p class="text-gray-500 dark:text-zinc-100/60 mt-2">If several languages coalesce, the
                                    grammar of the resulting language
                                    is more simple and regular than that of the individual</p>
                                <div class="mt-4">
                                    <button type="button"
                                            class="btn border-transparent bg-mandarin-500 mt-2 mr-2 shadow-md text-white shadow-mandarin-200 focus:ring focus:ring-mandarin-50 dark:shadow-zinc-600">
                                        Email
                                        Us
                                    </button>
                                    <button type="button"
                                            class="btn border-transparent bg-green-500 shadow-md text-white shadow-green-100 mt-2 waves-effect waves-light focus:ring focus:ring-green-100 dark:shadow-zinc-600">
                                        Send
                                        us a
                                        tweet
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                        <div class="border border-gray-50 rounded-md overflow-hidden dark:border-zinc-600">
                            <div class="relative">
                                <i
                                    class="bx bx-help-circle text-7xl text-mandarin-50/50 absolute ltr:-right-3 rtl:-left-3 -top-4 dark:text-mandarin-500/10"></i>
                            </div>
                            <div class="p-5">
                                <h5 class="text-mandarin-500">01.</h5>
                                <h5 class="mt-3 text-gray-700 dark:text-gray-100">What is Lorem Ipsum?</h5>
                                <p class="text-muted mt-3 mb-0 text-gray-500 dark:text-zinc-100/60">New common language
                                    will
                                    be more simple and regular than the existing European languages. It will be as
                                    simple as
                                    occidental.</p>
                            </div>
                        </div>

                        <div class="border border-gray-50 rounded-md overflow-hidden dark:border-zinc-600">
                            <div class="relative">
                                <i
                                    class="bx bx-help-circle text-7xl text-mandarin-50/50 absolute ltr:-right-3 rtl:-left-3 -top-4 dark:text-mandarin-500/10"></i>
                            </div>
                            <div class="p-5">
                                <h5 class="text-mandarin-500">02.</h5>
                                <h5 class="mt-3 text-gray-700 dark:text-gray-100">Where does it come from?</h5>
                                <p class="text-muted mt-3 mb-0 text-gray-500 dark:text-zinc-100/60">Everyone realizes
                                    why a
                                    new common language would be desirable one could refuse to pay expensive
                                    translators.
                                </p>
                            </div>

                        </div>

                        <div class="border border-gray-50 rounded-md overflow-hidden dark:border-zinc-600">
                            <div class="relative">
                                <i
                                    class="bx bx-help-circle text-7xl text-mandarin-50/50 absolute ltr:-right-3 rtl:-left-3 -top-4 dark:text-mandarin-500/10"></i>
                            </div>
                            <div class="p-5">
                                <h5 class="text-mandarin-500">03.</h5>
                                <h5 class="mt-3 text-gray-700 dark:text-gray-100">Where can I get some?</h5>
                                <p class="text-muted mt-3 mb-0 text-gray-500 dark:text-zinc-100/60">If several languages
                                    coalesce, the grammar of the resulting language is more simple and regular than that
                                    of
                                    the individual languages.</p>
                            </div>
                        </div>

                        <div class="border border-gray-50 rounded-md overflow-hidden dark:border-zinc-600">
                            <div class="relative">
                                <i
                                    class="bx bx-help-circle text-7xl text-mandarin-50/50 absolute ltr:-right-3 rtl:-left-3 -top-4 dark:text-mandarin-500/10"></i>
                            </div>
                            <div class="p-5">
                                <h5 class="text-mandarin-500">04.</h5>
                                <h5 class="mt-3 text-gray-700 dark:text-gray-100">Why do we use it?</h5>
                                <p class="text-muted mt-3 mb-0 text-gray-500 dark:text-zinc-100/60">Their separate
                                    existence
                                    is a myth. For science, music, sport, etc, Europe uses the same vocabulary.</p>
                            </div>
                        </div>

                        <div class="border border-gray-50 rounded-md overflow-hidden dark:border-zinc-600">
                            <div class="relative">
                                <i
                                    class="bx bx-help-circle text-7xl text-mandarin-50/50 absolute ltr:-right-3 rtl:-left-3 -top-4 dark:text-mandarin-500/10"></i>
                            </div>
                            <div class="p-5">
                                <h5 class="text-mandarin-500">05.</h5>
                                <h5 class="mt-3 text-gray-700 dark:text-gray-100">Where can I get some?</h5>
                                <p class="text-muted mt-3 mb-0 text-gray-500 dark:text-zinc-100/60">The point of using
                                    Lorem
                                    Ipsum is that it has a more-or-less normal they distribution of letters opposed to
                                    using
                                    content here. </p>
                            </div>
                        </div>

                        <div class="border border-gray-50 rounded-md overflow-hidden dark:border-zinc-600">
                            <div class="relative">
                                <i
                                    class="bx bx-help-circle text-7xl text-mandarin-50/50 absolute ltr:-right-3 rtl:-left-3 -top-4 dark:text-mandarin-500/10"></i>
                            </div>
                            <div class="p-5">
                                <h5 class="text-mandarin-500">06.</h5>
                                <h5 class="mt-3 text-gray-700 dark:text-gray-100">What is Lorem Ipsum?</h5>
                                <p class="text-muted mt-3 mb-0 text-gray-500 dark:text-zinc-100/60">To an English
                                    person, it
                                    will seem like simplified English, as a skeptical Cambridge friend of mine told me
                                    what
                                    Occidental</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
