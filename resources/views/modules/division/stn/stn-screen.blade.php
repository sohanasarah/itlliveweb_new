@extends('layouts.master')
@section('content-title')
STN Screen
@endsection
@section('content-body')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">STN</div>
        <div class="card-body ">
            <input type="hidden" name="ind_nbr" value="{{app('request')->get('ind_nbr')}}">

            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <p><b>Indent Number:</b> {{$ind_mstr->ind_nbr}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Indent Date:</b> {{$ind_mstr->ind_date}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Ship To Site:</b> {{$ind_mstr->ind_shipto}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Indent Status:</b> {{ucfirst(strtolower($ind_mstr->ind_status))}}</p>
                </div>
            </div>

            <form role="form" id="requestForm" method="post" action="{{route('stn-confirm')}}"">
                {{ csrf_field() }}
                {{-- without this line, post will not work  --}}
                <input type="hidden" name="ind_nbr" id="ind_nbr" value="{{$ind_mstr->ind_nbr}}" />
                <input type="hidden" name="prod_line" value="{{$ind_mstr->ind_prod_line}}">
                <input type="hidden" name="ship_to" value="{{$ind_mstr->ind_shipto}}">

                <div class="table-responsive p-0">
                    <table id="myTable" class="table table-striped table-bordered table-hover table-head-fixed">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Req No</th>
                                <th>Ship From</th>
                                <th>Item Code</th>
                                <th>Item Description</th>
                                <th>Select Lot</th>
                                <th>Available Qty(PCs)</th>
                                <th>Qty Requested(PCs)</th>
                                <th>Qty Open(PCs)</th>
                                <th>Enter STN Qty(PCs)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i=0;
                            $total_qty_req = 0;
                            $total_qty_open = 0;
                            $total_ship = 0;
                            @endphp

                            @foreach ($ind_details as $indd)

                            @php
                            $user = \Auth::user()->user_name;

                            $site = get_site_desc($indd->indd_shipfrom);
                            $shipfrom_desc = $site->si_desc;

                            $avail_lot = get_lot($indd->indd_part, $user);

                            $qty_open = $indd->qty_pc - $indd->qty_ship;

                            @endphp
                            

                            @if ($indd->indd_shipfrom == $user)
                            <tr class="row_data">
                                <input type="hidden" name="id[{{$i}}]" value="{{$indd->indd_id}}">
                                <input type="hidden" name="item[{{$i}}]" value="{{$indd->indd_part}}">
                                <input type="hidden" name="qty_req[{{$i}}]" value="{{$indd->qty_pc}}">

                                <td>{{$indd->indd_line}}</td>
                                <td>{{$indd->indd_req_nbr}}</td>
                                <td>{{$shipfrom_desc}}</td>
                                <td>{{$indd->indd_part}}</td>
                                <td>{{$indd->pt_desc}}</td>
                                <td>
                                    <select name="lot[{{$i}}]" id="lot" class="lot form-control" style="color:blue; width:100px">
                                        <option value="">Select</option>
                                        @foreach ($avail_lot as $lot)
                                            <option value="{{$lot->ld_lot}}">{{$lot->ld_lot}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input id="qty_avail" type="number" name="qty_avail[{{$i}}]" class="qty_avail form-control"
                                        style="color:blue; width:7em" readonly />
                                </td>
                                <td>{{number_format($indd->qty_pc)}}</td>
                                <td>{{number_format($qty_open)}}</td>
                                <td>
                                    <input id="qty_ship" type="number" min=0 max="999999" name="qty_ship[{{$i}}]"
                                        class="qty_ship form-control" value="0" style="color:blue; width:7em"/>
                                </td>
                            </tr>
                            @php
                                $total_qty_req += $indd->qty_pc;
                                $total_qty_open += $qty_open;
                                $total_ship += $indd->qty_ship;
                                $i++; //i will be incremented only for division
                            @endphp
                            @else
                                <tr>
                                    <td>{{$indd->indd_line}}</td>
                                    <td>{{$indd->indd_req_nbr}}</td>
                                    <td>{{$shipfrom_desc}}</td>
                                    <td>{{$indd->indd_part}}</td>
                                    <td>{{$indd->pt_desc}}</td>
                                    <td></td>
                                    <td></td>
                                    <td>{{ number_format($indd->qty_pc) }}</td>
                                    <td>{{ number_format($qty_open) }}</td>
                                    <td>{{ number_format($indd->qty_ship) }}</td>
                                </tr>
                            @endif
                            @endforeach
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td style="color: orangered; font-weight:bold">TOTAL</td>
                                <td></td>
                                <td></td>
                                <td style="color: orangered; font-weight:bold">
                                    {{ number_format($total_qty_req) }}</td>
                                <td style="color: orangered; font-weight:bold">
                                    {{ number_format($total_qty_open) }}</td>
                                <td><input type="number" class="total form-control" value="0" style="color:red; font-weight:bold; width:7em" readonly></td>
                            </tr>

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
                    <button type="button" class="btn btn-success btn-block subbtn" name="subbtn">
                        <i class="fa fa-ship"></i>Create Shipment
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('page-script')
<script type="text/javascript">
    function calcTotal() {
        var sum = 0;
        $(".qty_ship").each(function(i) {
            //alert($(this).val());
            sum += +$(this).val();

        });
        $(".total").val(sum);
    }
    $(document).ready(function() {
        //input validation
        $(".qty_ship").on("keypress keyup blur", function(event) {
            $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which > 57)) {
                event.preventDefault();
            }

        });

        //populate available qty based on selected lot
        $(document).on("change", ".lot", function() {
            var element_id = $(this).attr('name');
            var ix = element_id.substring(4, element_id.length - 1);

            var item = $('input[name="item[' + ix + ']"]').val();
            var lot = $(this).val();
            var user = "{{$user}}";
            
            if (lot) {
                $.ajax({
                    type: "GET",
                    url: "{{ url('/division/stn/stn/{site}/{item}/{lot}') }}",
                    data: {
                        site: user,
                        item: item,
                        lot: lot,
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                    }
                }).done(function(result) {
                        //console.log(result);
                        $.each(result, function(index, value) {
                            // alert(value.qty_avail);
                            qty_avail = value.qty_avail;
                            $('input[name="qty_avail[' + ix + ']"]').val(qty_avail);
                        });
                });
            }
            else {
                qty_avail = 0;
                $('input[name="qty_avail[' + ix + ']"]').val(qty_avail);
            }
        
        });

        //change grand total
        $(document).on("change", ".qty_ship", function() {
            var sum = 0;
            $(".qty_ship").each(function() {
                sum += +$(this).val();
            });
            $(".total").val(sum);
        });

        //submit form
        $('.subbtn').click(function() {
            var sum = 0;
            var check = true;
            var input_check = true;
            var msg = "";

            // Iterate over each input 
            $('.row_data').each(function(i) {
                // Compare two array
                var qty_ship = $('input[name="qty_ship[' + i + ']"]').val();
                var qty_avail = $('input[name="qty_avail[' + i + ']"]').val();
                var lot = $('input[name="lot[' + i + ']"]').val();
                
                sum += + qty_ship;
                
                var diff = (qty_ship - qty_avail);
                
                if (diff > 0) {
                    check = false;
                    msg = "Shipped Qty (" + qty_ship + ") exceeds qty available (" + qty_avail + "). Please Re-enter!";
                }

            });

            //if no input is given, sum will be 0. so form will not be submitted.!
            if (sum === 0) {
                check = false;
                // alert(sum);
                msg = "Please Select A Lot and Enter Shipped Qty!";
            }

            if (check === true) {
                if (confirm('Are you sure you want to do the shipment?')) {
                   $("#requestForm").submit();
                }
            } else {
                alert("Error: " + msg);
                event.preventDefault();
                check = true;
            }
        });

       
    });
</script>

@endsection