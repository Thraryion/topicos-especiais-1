<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ModeraBot</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{asset('AdminLTE/plugins/fontawesome-free/css/all.min.css')}}">
    <link rel="stylesheet" href="{{asset('AdminLTE/dist/css/adminlte.min.css')}}">
</head>
<body class="hold-transition layout-top-nav">
    <div class="wrapper">
        <x-layout.top-navbar />

        <div class="content-wrapper">
            <div class="content">
                <div class="container">
                    @livewire('pages.home')
                </div>
            </div>
        </div>

        <aside class="control-sidebar control-sidebar-dark">
        </aside>
        <x-layout.footer />
    </div>

    <script data-navigate-once src="{{asset('AdminLTE/plugins/jquery/jquery.min.js')}}"></script>
    <script data-navigate-once src="{{asset('AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script data-navigate-once src="{{asset('AdminLTE/dist/js/adminlte.min.js')}}"></script>
    
</body>
</html>