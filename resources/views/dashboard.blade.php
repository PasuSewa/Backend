@extends('layout.app', ['class' => 'register-page'])

@section('content')
    @include('layout.navbar')

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Registered Companies
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>Name</th>
                                        <th>URL for Logo</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td>Andrew Mike</td>
                                        <td>Develop</td>
                                        <td class="td-actions text-right">
                                            <button 
                                                type="button" 
                                                rel="tooltip" 
                                                class="btn btn-info btn-icon btn-sm " 
                                                data-original-title="" 
                                                data-toggle="tooltip" 
                                                data-placement="left" 
                                                title="Edit Company" 
                                            >
                                                <i class="ni ni-zoom-split-in pt-1"></i>
                                            </button>
                                            <button 
                                                    type="button" 
                                                    rel="tooltip" 
                                                    class="btn btn-danger btn-icon btn-sm " 
                                                    data-original-title=""
                                                    data-toggle="tooltip" 
                                                    data-placement="right" 
                                                    title="Delete Company" 
                                                >
                                                <i class="ni ni-fat-remove pt-1"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <nav aria-label="...">
                                <ul class="pagination">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1">
                                            <i class="fa fa-angle-left"></i>
                                            <span class="sr-only">Previous</span>
                                        </a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">2 <span class="sr-only">(current)</span></a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">3</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">
                                            <i class="fa fa-angle-right"></i>
                                            <span class="sr-only">Next</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection