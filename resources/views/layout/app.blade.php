@include('layout.head')
<body class="{{ $class ?? '' }}">
    <style>
        .card{
            border-radius: 10px !important;
        }
        .card-body{
            border-radius: 10px !important;
        }
        .card-header{
            border-top-left-radius: 10px !important;
            border-top-right-radius: 10px !important;
        }
        .card-footer{
            border-bottom-left-radius: 10px !important;
            border-bottom-right-radius: 10px !important;
        }
    </style>
    <main>
        @yield('content')
    </main>
</body>
@include('layout.foot')