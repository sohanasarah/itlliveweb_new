<!DOCTYPE html>
<html lang="en">

<head>
  @include('inc.header')
  <!-- STYLES -->
  @include('inc.styles')
  <!-- REQUIRED SCRIPTS -->
  @include('inc.scripts')
</head>

<body class="hold-transition sidebar-mini layout-navbar-fixed sidebar-collapse" style="height:auto">
  
  <!-- wrapper -->
  <div class="wrapper">
    @include('inc.navbar')
    @include('inc.sidebar')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      
      <!-- Content Header (Page header) -->
      <div class="content-header">
        <div class="container-fluid">
          <div class="row mb-2">
            <div class="col-sm-6">
              <h1 class="m-0 text-blue">
                 @yield('content-title')
              </h1>
            </div><!-- /.col -->
    
            <div class="col-sm-6">
              <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{route("home")}}">Home</a></li>
                <li class="breadcrumb-item active">
                  @yield('content-title')
                </li>
              </ol>
            </div><!-- /.col -->
          </div><!-- /.row -->
        </div><!-- /.container-fluid -->
      </div>
      <!-- /.content-header -->

      @yield('content-body')

    </div>
    <!-- /.content-wrapper -->

    
    @yield('page-script')
    
    @include('inc.dynamic-sidebar')

    @include('inc.footer')
  </div>
  <!-- ./wrapper -->

</body>

</html>