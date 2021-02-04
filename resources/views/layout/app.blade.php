@include('layout.head')
<body class="{{ $class ?? '' }}">
    <main>
        @yield('content')
    </main>
</body>
@include('layout.foot')