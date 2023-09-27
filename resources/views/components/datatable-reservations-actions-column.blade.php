@props([
    'deleteConfirmationMessage' => __('Are you sure?'),
])
<style>
.ml-5{
    grid-column-gap: 5px;
}
</style>
<div class="flex justify-center items-center ml-5">
    

    <a class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"
        href="{{ route('reservations.show', $getState()) }}"><i
            class="bx bx-show block text-lg"></i>
    </a>

    <form class="form-actions" action="{{ route('reservations.cancel', $getState()) }}" method="GET">                
        @csrf
        <button type="submit"
            class="btn text-neutral-800 bg-neutral-100 border-neutral-100 hover:text-violet-500 hover:bg-neutral-900 hover:border-neutral-900 focus:text-violet-500 focus:bg-neutral-900 focus:border-neutral-900 focus:ring focus:ring-neutral-500/10 active:bg-neutral-900 active:border-neutral-900 dark:bg-neutral-500/20 dark:border-transparent dark:text-gray-100"><i
                class="mdi mdi-cancel block text-lg"></i> </button>
    </form>
</div>
