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
    <div 
        class="modal fade" 
        id="modal-edit-company" 
        tabindex="-1" 
        role="dialog" 
        aria-labelledby="modal-edit-company-title" 
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form class="modal-content" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Edit Company</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row justify-content-around">
                        <div class="form-group col-lg-12">
                            <label for="company-name" class="col-form-label">Company Name:</label>
                            <input 
                                type="text" 
                                class="form-control" 
                                value=""
                                id="company-name"
                            />
                        </div>
                        <div class="form-group col-lg-12">
                            <label for="company_logo">Select Logo</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="company_logo" lang="en">
                                <label class="custom-file-label" for="company_logo"></label>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <td>
                                <a 
                                    href="#" 
                                    target="_blank"
                                    class="btn-link text-primary"
                                    id="company-old-logo"
                                >
                                    See Old Logo
                                </a>
                            </td>
                        </div>
                        <input type="hidden" name="company_id" value="" id="company-id">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" type="submit">Save</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            const buttons = document.querySelectorAll('.open-modal')

            buttons.forEach(button => {
                button.addEventListener('click', e => {
                    let companyName = e.target.getAttribute('company-name')

                    let companyId = e.target.getAttribute('company-id')

                    let companyLogo = e.target.getAttribute('company-logo')

                    editCompany(companyName, companyId, companyLogo)
                })
            });
        })

        function editCompany(name, id, logo)
        {
            console.log({
                name,
                id,
                logo
            })
            document.getElementById('company-id').setAttribute('value', id)

            document.getElementById('company-name').setAttribute('value', name)

            document.getElementById('company-old-logo').setAttribute('href', logo)

            openModal()
        }

        function openModal()
        {
            $('#modal-edit-company').modal('show')
        }
    </script>
@endsection