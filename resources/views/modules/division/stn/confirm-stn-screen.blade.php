@extends('layouts.master')
@section('content-title')
Confirmation Screen
@endsection
@section('content-body')
<div class="col-sm-12 col-md-8">
    <div class="card">
        <div class="card-header">
            <h5>Confirm STN</h5>
        </div>
        <div class="card-body ">
            <form role="form" id="requestForm" method="post">
                {{ csrf_field() }}
                {{-- without this line, ajax post will not work  --}}

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="form-group">
                            <label for="transporter" class="control-label">Transporter</label>
                            <select name="transporter" id="transporter" class="form-control">
                                <option value="">--Select--</option>
                                @foreach ($transports as $transport)
                                <option value="{{$transport->code_value}}">{{$transport->code_cmmt}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label for="truck_no" class="control-label">Truck No</label>
                            <input type="text" name="truck_no" id="truck_no" class="form-control" />
                        </div>
                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="form-group">
                            <label for="vat_challan" class="control-label">Vat Challan</label>
                            <input type="text" name="vat_challan" id="vat_challan" class="form-control"
                                value="{{$doNbr}}" />
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="table-responsive">
                            <table id="myTable" width="100%" class="table table-bordered" border="0" cellspacing="0"
                                cellpadding="0">
                                <thead>
                                    <tr>
                                        <th>Item Code</th>
                                        <th>Item Description</th>
                                        <th>Qty Requested (PCs)</th>
                                        <th>Lot</th>
                                        <th>Shipped Qty (PCs)</th>

                                    </tr>
                                </thead>
                                <tbody>

                                    @php
                                    $i=0;
                                    $shipfrom = \Auth::user()->user_name;
                                    @endphp

                                    <input type="hidden" name="ind_nbr" value="{{ $postData['ind_nbr'] }}" />
                                    <input type="hidden" name="prod_line" value="{{ $postData['prod_line'] }}" />
                                    <input type="hidden" name="shipfrom" value="{{ $shipfrom}}" />
                                    <input type="hidden" name="shipto" value="{{ $postData['ship_to'] }}" />
                                    
                                    @foreach ($postData['id'] as $idx => $ind_id)

                                    @php
                                    $item_details = get_item_details($postData['item'][$idx]);
                                    $item_desc = $item_details->pt_desc;
                                    @endphp

                                    <input type="hidden" name="ind_id[{{$i}}]" value="{{ $ind_id }}" />
                                    <input type="hidden" name="item[{{$i}}]" value="{{ $postData['item'][$idx] }}" />
                                    <input type="hidden" name="lot[{{$i}}]" value="{{ $postData['lot'][$idx] }}" />
                                    <input type="hidden" name="qty_ship[{{$i}}]" value="{{ $postData['qty_ship'][$idx] }}" />

                                    @if ($postData['qty_ship'][$idx] > 0)
                                        <tr>
                                            <td>{{$postData['item'][$idx]}}</td>
                                            <td>{{$item_desc}}</td>
                                            <td>{{$postData['qty_req'][$idx]}}</td>
                                            <td>{{$postData['lot'][$idx]}}</td>
                                            <td>{{$postData['qty_ship'][$idx]}}</td>
                                        </tr>
                                    @endif
                                   
                                    @php
                                    $i++;
                                    @endphp

                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-footer">
            <div class="row">
                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-danger btn-block" onclick="window.history.back();">
                        <i class="fa fa-angle-double-left"></i>Back
                    </button>
                </div>

                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-success btn-block subbtn" name="subbtn">
                        <i class="fa fa-ship"></i>Confirm & Save
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
        $('.subbtn').click(function() {
            var check = 0;
        
            if ($('#transporter').val() === "" || $('#truck_no').val() === "" || $('#driver_contact').val() === "") {
                check++;
                $('#transporter').css('border', '2px solid Red');
                $('#truck_no').css('border', '2px solid Red');
                $('#driver_contact').css('border', '2px solid Red');
                alert('Please fill up all the fields!');
            } else {
                check === 0;
            }

            if (check === 0) {
                $('.submit').attr("disabled", true);
                if (confirm('Are you sure to submit?')) {
                    $.ajax({
                        url: "{{route('stn-save')}}",
                        type: "POST",
                        data: $('#requestForm').serialize(),
                        statusCode: {
                            404: function() {
                                alert('page not found');
                            }
                        },
                        success: function(data) {
                            console.log(data);
                            alert(data.msg);
                            window.location.href = "{{route('open-indents')}}";
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                            console.log(textStatus);
                            alert(textStatus + jqXHR.responseText);
                            //location.reload();
                        }
                    });
                }
                
            }
        });
    });
</script>

@endsection