@extends('layouts.master')
@section('content-title')
GRR Screen
@endsection
@section('content-body')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">GRR</div>
        <div class="card-body ">
            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <p><b>DO Number: </b> {{$ds_mstr->ds_donbr}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Shipment Date:</b> {{$ds_mstr->ds_ship_date}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Ship From Site:</b> {{$ds_mstr->ds_ship_from}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>DO Status:</b> {{ucfirst(strtolower($ds_mstr->ds_status))}}</p>
                </div>
            </div>

            <form role="form" id="requestForm" method="post">
                {{ csrf_field() }}

                <input type="hidden" name="user" id="user" value="{{Auth::user()->user_name}}" />
                <input type="hidden" name="do_nbr" id="do_nbr" value="{{$ds_mstr->ds_donbr}}" />
                <input type="hidden" name="transporter" id="transporter" value="{{$ds_mstr->ds_transporter}}" />

                <div class="table-responsive p-0">
                    <table id="myTable" class="table table-striped table-bordered table-hover table-head-fixed">
                        <thead>
                            <tr>
                                <th width="2%">Req Nbr</th>
                                <th width="2%">Item</th>
                                <th width="4%">Item Desc</th>
                                <th width="2%">Lot</th>
                                <th width="2%">STN Qty</th>
                                <th style="color: red" class="text-center" width="8%">Enter Receipt Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i=0;
                            @endphp

                            @foreach ($dsd_det as $do_details)

                            <tr class="row_data">
                                <input type="hidden" name="do_id[{{$i}}]" id="do_id" value="{{$do_details->dsd_id}}">
                                <td>{{$do_details->dsd_req_nbr}}</td>
                                <td>{{$do_details->dsd_part}}</td>
                                <td>{{$do_details->pt_desc}}</td>
                                <td>{{$do_details->dsd_lot}}</td>
                                <td>
                                    <input type="text" size="10" name="qty_ship[{{$i}}]" class="qty_ship"
                                        value="{{$do_details->dsd_qty_ship}}" readonly />
                                </td>
                                <td>
                                    <input type="number" size="10" min="0" max="999999" name="qty_good[{{$i}}]"
                                        class="qty_good" value="{{$do_details->dsd_qty_ship}}" style="color: red" />
                                </td>
                            </tr>

                            @php
                            $i++;
                            @endphp

                            @endforeach

                        </tbody>
                    </table>
                </div>
            </form>

        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-primary btn-block submit" onclick="window.history.back();"><i
                            class="fa fa-angle-double-left"></i>Go Back
                    </button>
                </div>

                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-success btn-block submit" name="submit">
                        <i class="fa fa-ship"></i> Confirm GRR
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('page-script')
<script type="text/javascript">
    $(document).ready(function() {
    $("#loading-div-background").css({
        opacity: 1
    });
    $('.submit').click(function(event) {
        var total_sum = 0;
        var total_ship = 0;
        var check = true;
        var msg = "";
        var total_count = 0;
        var input_count = 0;
        $('.row_data').each(function(i) {

            var qty_ship = $('input[name="qty_ship[' + i + ']"]').val();
            var qty_good = $('input[name="qty_good[' + i + ']"]').val();
            var qty_total = qty_good;
          

            total_count++;
            if (qty_total > 0) {
                input_count++;
            }
            if (parseInt(qty_total) !== parseInt(qty_ship)) {
                check = false;
                msg = "Input Qty (" + qty_total + ") must be equal to STN QTY (" + qty_ship +")!";
            }
        });

        if (input_count === 0) {
            check = false;
            msg = "Input Can Not Be Empty!";
        } else {
            if (total_count !== input_count) {
                check = false;
                 msg = "You Must Receive The Full Shipment!";
            }
        }
        if (check === true) {
            $('.submit').attr("disabled", true);
            if (confirm('Are you sure you want to do the shipment?')) {
                $.ajax({
                    url: "{{route('grr-save')}}",
                    type: "POST",
                    data: $('#requestForm').serialize(),
                    statusCode: {
                        404: function() {
                            alert('page not found');
                        }
                    },
                    beforeSend: function() {
                        $("#loading-div-background").show();
                    },
                    success: function(data) {
                        console.log(data);
                        alert(data.msg);
                        window.location.href("route('home')");
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                        console.log(textStatus);
                        alert(textStatus + jqXHR.responseText);
                        //location.reload();
                    },
                    complete: function() {
                        $("#loading-div-background").hide();
                    }
                });
            }

        } else {
            alert(msg);
            event.preventDefault();
            check = true;

        }
    });

    $('#myTable').DataTable({
        responsive: true,
        sorting: true,
        paging: false,
        searching: false
    });
});
   
</script>

@endsection