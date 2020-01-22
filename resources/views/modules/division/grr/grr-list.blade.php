@extends('layouts.master')
@section('content-title')
Pending GRR
@endsection
@section('content-body')
<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Default box -->
                <div class="card">
                    <div class="card-header">
                        <!-- SEARCH FORM -->
                        <form class="form-inline ml-4">
                            <div class="input-group input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>
                                </div>
                                <input type="text" name="start_date" id="start_date" placeholder="From Date"
                                    class="form-control" value="{{app('request')->get('start_date')}}" />

                                <input type="text" name="end_date" id="end_date" placeholder="To Date"
                                    class="form-control" value="{{app('request')->get('end_date')}}" />

                                <div class="input-group-append">
                                    <button class="btn btn-navbar btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <!-- /.input group -->
                        </form>
                        <!-- /.form group -->
                    </div>

                    <form role="form" id="requestForm">
                        <div class="card-body">
                            <input type="hidden" name="start_date" value="{{app('request')->get('start_date')}}" />
                            <input type="hidden" name="end_date" value="{{app('request')->get('end_date')}}" />

                            <div class="form-group row">
                                <div class="col-md-12 col-lg-12">
                                    <div class="table-responsive-md">
                                        <table id="myTable" width="100%"
                                            class="table table-sm table-hover table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>DO Number</th>
                                                    <th>Indent Nbr</th>
                                                    <th>Ship From</th>
                                                    <th>Shipped Qty</th>
                                                    <th>Ship Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                                @php
                                                $i = 0;
                                                $total_qty = 0;
                                                $total_kg = 0;
                                                $total_ship = 0;
                                                @endphp

                                                @foreach ($do_ships as $do_ship)

                                                @php
                                                $i++;
                                                $ship_from = get_site_desc($do_ship->ds_ship_from);
                                                @endphp

                                                <tr>
                                                    <td><a
                                                            href="{{route('grr-view', ['do_nbr'=>$do_ship->ds_donbr])}}">{{ $do_ship->ds_donbr }}</a>
                                                    </td>
                                                    <td>{{ $do_ship->ds_ind_nbr }}</td>
                                                    <td>{{ $ship_from->si_desc}}</td>
                                                    <td>{{ number_format($do_ship->qty_ship) }}</td>
                                                    <td>{{ $do_ship->ds_ship_date }}</td>
                                                </tr>

                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-body -->
                    </form>
                </div>
                <!-- /.card -->
            </div>
        </div>
        <!-- /.row-->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->

@endsection

@section('page-script')
<script type="text/javascript">
    //Date range picker
    $('input[name="start_date"]').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD'
        },
        singleDatePicker: true,
        showDropdowns: true,
    });
    $('input[name="end_date"]').daterangepicker({
        locale: {
           format: 'YYYY-MM-DD'
        },
        singleDatePicker: true,
        showDropdowns: true,
    });

    $('input[name="start_date"]').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    
    $('input[name="start_date"]').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });

    $('input[name="end_date"]').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    
    $('input[name="end_date"]').on('cancel.daterangepicker', function(ev, picker) {
    $(this).val('');
    });

</script>


@endsection