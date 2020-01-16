@extends('layouts.master')
@section('content-title')
Pending Shipments
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
                                <select name="depot" id="depot" class="form-control form-control-navbar">
                                    <option value="">Filter By Depot</option>
                                    @foreach ($depots as $depot)
                                    <option value="{{$depot->si_site}}">{{$depot->si_desc}}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append">
                                    <button class="btn btn-navbar btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <form role="form" id="requestForm">
                        <div class="card-body">
                            <input type="hidden" name="txt_depot" value="{{app('request')->get('depot')}}">

                            <p><i><b>Please Select An Indent To Allocate First. Only Allocated Indents Can Be Shipped.</b></i></p>

                            <div class="form-group row">
                                <div class="col-md-12 col-lg-12">
                                    <div class="table-responsive-md">
                                        <table id="myTable" width="100%"
                                            class="table table-sm table-hover table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Indent No</th>
                                                    <th>Indent Date</th>
                                                    <th>Required Date</th>
                                                    <th>Prod Line</th>
                                                    <th>Ship From</th>
                                                    <th>Ship To</th>
                                                    <th>No of Items</th>
                                                    <th>Requested Qty(PCs)</th>
                                                    <th>Shipped Qty(PCs)</th>
                                                    <th>Status</th>
                                                    <th></th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $i=0;
                                                @endphp
                                                @foreach ($indents as $indent)
                                                @php
                                                $i++;
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <a href="{{route('view-indent', ['ind_nbr'=>$indent->ind_nbr])}}">{{ $indent->ind_nbr }}</a>
                                                    </td>
                                                    <td>{{ $indent->ind_date }}</td>
                                                    <td>{{ $indent->ind_req_date }}</td>
                                                    <td>{{ $indent->ind_prod_line }}</td>
                                                    <td>{{ $indent->ind_shipfrom }}</td>
                                                    <td>{{ $indent->ind_shipto }}</td>
                                                    <td>{{ $indent->items }}</td>
                                                    <td>{{ number_format($indent->qty_pc) }}</td>
                                                    <td>{{ number_format($indent->qty_ship) }}</td>
                                                    <td class="status" id="status">{{ucfirst(strtolower($indent->ind_status))}}</td>
                                                    <td>
                                                        <button type="button" onclick="location.href='{{route('allocate', ['ind_nbr'=>$indent->ind_nbr])}}'" class="btn btn-info btn-allocate">Allocate</button>
                                                    </td>
                                                    <td>
                                                        <button type="button" onclick="location.href='{{route('stn', ['ind_nbr'=>$indent->ind_nbr, 'ind_status'=> $indent->ind_status]) }}'" class="btn btn-success btn-stn" >STN</button>
                                                    </td>
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
<script>
    $(document).ready(function() {
        var ind_status = "";

        $('table tr').each(function(i) {
            ind_status = $(this).find('.status').html();
            if (ind_status === "Allocated") {
                $(this).find('.btn-allocate').prop('disabled', true);
                $(this).find('.btn-stn').prop('disabled', false);
            } else if (ind_status === "Pending") {
                $(this).find('.btn-allocate').prop('disabled', false);
                $(this).find('.btn-stn').prop('disabled', true);
            }
        });

    });
</script>

@endsection