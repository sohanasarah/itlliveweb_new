@extends('layouts.master')

@section('content-title')
Sales By SKU By Date
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
                    <!-- /.card header -->

                    <div class="card-body">
                        <div class="col-md-12 col-lg-12">
                            <div class="table-responsive-md">
                                <table id="myTable" width="100%"
                                    class="table table-bordered table-sm table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Sales Order</th>
                                            <th>Depot Name</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Product Line</th>
                                            <th>Item Code</th>
                                            <th>Item Description</th>
                                            <th>Lot</th>
                                            <th>Price</th>
                                            <th>Qty(PC)</th>
                                            <th>Qty(KG)</th>
                                            <th>Amount(Tk)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($sales as $row_sales)
                                        @php
                                        $site_desc = get_site_desc($row_sales->sod_depot);
                                        //get_customer_desc($conn, $row_sales['sod_cust']);
                                        @endphp

                                        <tr>

                                            <td><?php echo $row_sales->sod_nbr ?></td>
                                            <td><?php echo $site_desc->si_desc ?></td>
                                            <td><?php echo $row_sales->sod_date ?></td>
                                            <td><?php echo $row_sales->sod_cust ?></td>
                                            <td><?php echo $row_sales->pt_prod_line ?></td>
                                            <td><?php echo $row_sales->sod_part ?></td>
                                            <td><?php echo $row_sales->pt_desc ?></td>
                                            <td><?php echo $row_sales->sod_lot ?></td>
                                            <td><?php echo $row_sales->sod_netprice ?></td>
                                            <td><?php echo $row_sales->total_qty ?></td>
                                            <td><?php echo $row_sales->total_kg ?></td>
                                            <td><?php echo number_format($row_sales->total_amount, 2); ?></td>
                                        </tr>
                                        @endforeach

                                    </tbody>
                                </table>
                            </div>
                            <!-- /.table-responsive-->
                        </div>
                        <!-- /.col-->
                    </div>
                    <!-- /.card-body -->


                </div>
                <!-- /.card -->
            </div>
            <!-- /. col -->
        </div>
        <!-- /.row-->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->
@endsection

@section('page-script')
<script>

$(document).ready(function() {
    var table = $('#myTable').DataTable({
       dom: 'Blfrtip',
       buttons:[ 'copy', 'csv', 'excel', 'pdf', 'colvis'],
    //    buttons: [{
    //         extend: 'collection',
    //         text: 'Export',
    //         buttons:[ 'copy', 'csv', 'excel', 'pdf', 'colvis'],
    //    }]
       
    });

    //table.buttons().container().appendTo( '#myTable .col-md-6:eq(0)' );

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
   
});
</script>
@endsection