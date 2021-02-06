<div 
    class="modal fade" 
    id="modal-edit-company" 
    tabindex="-1" 
    role="dialog" 
    aria-labelledby="modal-edit-company-title" 
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered" role="document">
        <form 
            class="modal-content" 
            action="{{route('update_company')}}" 
            enctype="multipart/form-data" 
            method="post" 
        >
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
                            name="company_name"
                        />
                    </div>
                    <div class="form-group col-lg-12">
                        <label for="company_logo">Select Logo</label>
                        <div class="custom-file">
                            <input 
                                type="file" 
                                class="custom-file-input" 
                                id="company_logo" 
                                lang="en"
                                name="company_logo"
                            />
                            <label class="custom-file-label" for="company_logo"></label>
                        </div>
                    </div>
                    <div class="col-lg-4">
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
                <input class="btn btn-success" type="submit" value="Save">
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