<nav class="navbar navbar-expand-lg navbar-dark bg-default py-0 fixed-top">
    <div class="container">
        <a class="navbar-brand" href="{{route('home')}}">Dashboard</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-default" aria-controls="navbar-default" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbar-default">
            <div class="navbar-collapse-header">
                <div class="row">
                    <div class="col-6 collapse-brand"> 
                        <a href="{{route('home')}}">
                            Dashboard
                        </a>
                    </div>
                    <div class="col-6 collapse-close">
                        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbar-default" aria-controls="navbar-default" aria-expanded="false" aria-label="Toggle navigation">
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>
            </div>
            
            <ul class="navbar-nav ml-lg-auto">
                <li class="nav-item">
                    <a href="{{route('statistics')}}" class="nav-link">Statistics</a>
                </li>
                <li class="nav-item">
                    <a 
                        class="nav-link nav-link-icon" 
                        href="{{route('logout')}}" 
                        data-toggle="tooltip" 
                        data-placement="bottom" 
                        title="Logout" 
                        data-container="body" 
                        data-animation="true"
                    >
                        <i class="ni ni-lock-circle-open"></i>
                        <span class="nav-link-inner--text d-lg-none">Logout</span>
                    </a>
                </li>
            </ul>
            
        </div>
    </div>
</nav>