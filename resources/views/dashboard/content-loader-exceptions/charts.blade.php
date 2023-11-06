@extends('layouts.master')
@section('title')
    {{ __('Exceptions Report') }}
@endsection
@section('css')
    <link href="{{ URL::asset('build/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css') }}"
          rel="stylesheet"
          type="text/css">

    <link href="{{ URL::asset('build/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet"
    >
@endsection
@section('content')

    <!-- -->
    <x-page-title title="Exceptions Report Charts" pagetitle="index"/>

    <div class="grid grid-cols-12 gap-5">
        <div class="col-span-12">
            <div class="card dark:bg-zinc-800 dark:border-zinc-600">
                <div class="card-body relative overflow-x-auto">
                    <canvas id="myChart"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
       var data = {
            labels: <?=json_encode($dates)?>,
            datasets: [
                {
                    label: "Success",
                    backgroundColor: "rgba(75, 192, 192, 0.2)",
                    borderColor: "rgba(75, 192, 192, 1)",
                    borderWidth: 1,
                    data: <?=json_encode($success)?> // Значение первого бара
                },
                {
                    label: "Error",
                    backgroundColor: "rgba(255, 99, 132, 0.2)",
                    borderColor: "rgba(255, 99, 132, 1)",
                    borderWidth: 1,
                    data: <?=json_encode($errors)?> // Значение второго бара
                }
            ],
            
        };

        var options = {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        };
        var ctx = document.getElementById('myChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: options
        });
    </script>
@endsection
