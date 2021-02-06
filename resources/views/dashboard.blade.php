@extends('layout.app', ['class' => 'register-page'])

@section('content')
    @include('layout.navbar')

    <section class="section mt-5">
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
                                    @foreach ($companies as $company)
                                        <tr>
                                            <td class="text-center">{{$company->id}}</td>
                                            <td>
                                                {{$company->name}}
                                            </td>
                                            <td>
                                                <a 
                                                    href="{{$company->url_logo}}" 
                                                    target="_blank" 
                                                    class="btn-link text-primary"
                                                >
                                                    See Logo
                                                </a>
                                            </td>
                                            <td class="td-actions text-right">
                                                <button 
                                                    type="button" 
                                                    rel="tooltip" 
                                                    class="btn btn-info btn-icon btn-sm open-modal" 
                                                    data-original-title="" 
                                                    data-toggle="tooltip" 
                                                    data-placement="left" 
                                                    title="Edit Company" 
                                                    company-name="{{$company->name}}"
                                                    company-id="{{$company->id}}"
                                                    company-logo="{{$company->url_logo}}"
                                                >
                                                    <i 
                                                        class="ni ni-zoom-split-in pt-1"
                                                        company-name="{{$company->name}}"
                                                        company-id="{{$company->id}}"
                                                        company-logo="{{$company->url_logo}}"
                                                    >
                                                    </i>
                                                </button>
                                                <a 
                                                    rel="tooltip" 
                                                    class="btn btn-danger btn-icon btn-sm text-white"
                                                    data-toggle="tooltip" 
                                                    data-placement="right" 
                                                    title="Delete Company"
                                                    href="{{route('delete_company', $company->id)}}" 
                                                >
                                                    <i class="ni ni-fat-remove pt-1"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            {{ $companies->links() }}
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card">
                        <form action="" method="post" class="card-body pt-4 pb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="company_logo">Company Name</label>
                                        <input type="email" class="form-control" id="exampleFormControlInput1" placeholder="name@example.com">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="company_logo">Select Logo</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="company_logo" lang="en">
                                            <label class="custom-file-label" for="company_logo"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 pt-4">
                                    <button class="btn btn-success mt-2">
                                        Create Company
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Suggestions
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>User Name</th>
                                        <th>Suggestion</th>
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
                                                class="btn btn-success btn-icon btn-sm " 
                                                data-original-title="" 
                                                data-toggle="tooltip" 
                                                data-placement="left" 
                                                title="Publish Suggestion" 
                                            >
                                                <i class="ni ni-send pt-1"></i>
                                            </button>
                                            <button 
                                                    type="button" 
                                                    rel="tooltip" 
                                                    class="btn btn-danger btn-icon btn-sm " 
                                                    data-original-title=""
                                                    data-toggle="tooltip" 
                                                    data-placement="right" 
                                                    title="Discard Suggestion" 
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
    
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">
                                Feedback Received
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th>User Name</th>
                                        <th>Feedback Body</th>
                                        <th>Calification</th>
                                        <th class="text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center">1</td>
                                        <td>Andrew Mike</td>
                                        <td>Develop</td>
                                        <td>10/10</td>
                                        <td class="td-actions text-right">
                                            <button 
                                                type="button" 
                                                rel="tooltip" 
                                                class="btn btn-success btn-icon btn-sm " 
                                                data-original-title="" 
                                                data-toggle="tooltip" 
                                                data-placement="left" 
                                                title="Publish Feedback" 
                                            >
                                                <i class="ni ni-send pt-1"></i>
                                            </button>
                                            <button 
                                                    type="button" 
                                                    rel="tooltip" 
                                                    class="btn btn-danger btn-icon btn-sm " 
                                                    data-original-title=""
                                                    data-toggle="tooltip" 
                                                    data-placement="right" 
                                                    title="Discard Feedback" 
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
    
    @include('sections.edit-company-modal')
@endsection