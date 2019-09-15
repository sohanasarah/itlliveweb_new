<!DOCTYPE html>
<html lang="en">

<head>
  @include('inc.header')
  <!-- STYLES -->
  @include('inc.styles')
</head>

<body class="hold-transition sidebar-mini">
  <!-- wrapper -->
  <div class="wrapper">
    @include('inc.navbar')
    @include('inc.sidebar')

    @yield('content')
    
    @include('inc.footer')
  </div>
  <!-- ./wrapper -->

  <!-- REQUIRED SCRIPTS -->
  @include('inc.scripts')
</body>

</html>