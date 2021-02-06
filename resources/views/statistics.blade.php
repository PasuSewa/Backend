@extends('layout.app')

@section('content')
    @include('layout.navbar')

    <section class="section mt-5">
        <div class="container">
            <div class="row justify-content-around">

                <div class="col-lg-12 my-4 text-center">
                    <h2 class="title">
                        Statistics
                    </h2>
                </div>

                @foreach ($statistics as $data)
                    <div class="col-lg-6">
                        <div class="card card-stats">
                            <!-- Card body -->
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <h5 class="card-title text-capitalize text-muted mb-0">{{$data['title']}}</h5>
                                        <span class="h2 font-weight-bold mb-0">{{number_format($data['count'])}}</span>
                                    </div>
                                    <div class="col-auto">
                                        <div class="icon icon-shape bg-gradient-info text-white rounded-circle shadow">
                                            <i class="ni ni-{{$data['icon']}}"></i>
                                        </div>
                                    </div>
                                </div>
                                {{-- <p class="mt-3 mb-0 text-sm">
                                    <span class="text-success mr-2"><i class="fa fa-arrow-up"></i> 3.48%</span>
                                    <span class="text-nowrap">Since last month</span>
                                </p> --}}
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>
    </section>
@endsection