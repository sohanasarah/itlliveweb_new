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
                            <div class="table-responsive">
                                <table id="myTable" width="100%" class="table table-bordered table-hover table-striped">
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

@endsection