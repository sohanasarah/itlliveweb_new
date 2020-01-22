@extends('layouts.master')
@section('content-title')
Indent Allocate Screen
@endsection
@section('content-body')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">Allocate</div>
        <div class="card-body">
            <input type="hidden" name="ind_nbr" value="{{app('request')->get('ind_nbr')}}">

            <div class="row">
                <div class="col-md-6 col-lg-6">
                    <p><b>Indent Number: </b> {{$ind_mstr->ind_nbr}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Indent Date: </b> {{$ind_mstr->ind_date}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Ship To Site: </b> {{$ind_mstr->ind_shipto}}</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p><b>Indent Status: </b> {{ucfirst(strtolower($ind_mstr->ind_status))}}</p>
                </div>
            </div>

            <form role="form" id="requestForm" method="post">
                {{ csrf_field() }}   
                {{-- without this line, ajax post will not work  --}}
                <input type="hidden" name="ind_nbr" id="ind_nbr" value="{{$ind_mstr->ind_nbr}}" />
                <input type="hidden" name="prod_line" value="{{$ind_mstr->ind_prod_line}}">
                <input type="hidden" name="ship_to" value="{{$ind_mstr->ind_shipto}}">

                <div class="table-responsive">
                    <table id="myTable" width="100%" class="table table-striped table-bordered" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Line</th>
                                <th>Req No</th>
                                <th>Item Code</th>
                                <th>Item Description</th>
                                <th>Ship To</th>
                                <th>Ship From</th>
                                <th>Qty Requested (PCs)</th>
                                <th>Status</th>
                                <th>Close</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $i=0;
                            @endphp

                            @foreach ($ind_details as $indd)

                            @php
                            $site = get_site_desc($indd->indd_shipto);
                            $shipto_desc = $site->si_desc;
                            $i++;
                            @endphp

                            <tr class="row_data">
                                <td>{{$indd->indd_line}}</td>
                                <td>{{$indd->indd_req_nbr}}</td>
                                <td>{{$indd->indd_part}}</td>
                                <td>{{$indd->pt_desc}}</td>
                                <td>{{$shipto_desc}}</td>
                                <td>
                                    <select name="ship_from[{{$i}}]" id="ship_from" class="ship_from form-control">
                                        <option value="">--Select--</option>
                                        @foreach ($sources as $source)
                                        <option value="{{$source->si_site}}">{{$source->si_desc}}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input name="ind_qty[{{$i}}]" id="ind_qty" type="number" min=0 max="999999"
                                        class="ind_qty form-control" value="{{ $indd->indd_qty_req}}" />
                                </td>
                                <td>{{ucfirst(strtolower($indd->indd_status))}}</td>
                                <td>
                                    @if ($ind_mstr->ind_cancelable == 'true')
                                    <a id="button" class="btn btn-danger" role="button"
                                        onclick="close_indent('{{ $indd->indd_nbr}} ', '{{ $indd->indd_id}}')">
                                        Close
                                    </a>
                                    @endif
                                </td>
                                <input type="hidden" name="ind_id[{{$i}}]" value="{{$indd->indd_id}}">
                            </tr>
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
                            class="fa fa-angle-double-left"></i>Go Back</button>
                </div>
                @if($ind_mstr->ind_cancelable == 'true')
                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-danger btn-block closebtn" name="closebtn"
                        onclick="close_indent('{{$ind_mstr->ind_nbr}}', '')"><i class="fa fa-cancel"></i>Close
                        Indent</button>
                </div>
                @endif
                <div class="col-xs-6 col-sm-6 col-md-3 col-lg-3">
                    <button type="button" class="btn btn-success btn-block subbtn" name="subbtn"><i
                            class="fa fa-ship"></i>Allocate</button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('page-script')
<script type="text/javascript">
    function close_indent(nbr, id) {
        if (confirm('Are You Sure You Want To Close This Indent?')) {
            $.ajax({
                type: 'POST',
                url: 'modules/stn/close_indent.php',
                data: {
                    ind_nbr: nbr,
                    ind_id: id
                },
                success: function(data) {
                    var json = $.parseJSON(data);
                    alert(json.message);
                    // alert(data);
                    window.location.href = 'index.php?mod=stn&act=list';
                },
                error: function() {
                    alert('error');
                }
            });
        }
    }
    
    $(document).ready(function() {
    
        //input validation
        $(".ind_qty input").on("keypress keyup blur", function(event) {
            $(this).val($(this).val().replace(/[^\d].+/, ""));
            if ((event.which < 48 || event.which> 57)) {
                event.preventDefault();
            }
        });
    
        $('.subbtn').click(function() {
            var shipfrom = 0;
            var check = true;
            var msg = "";
            // $('#selectorId option:selected').val();
            $('.row_data #ship_from').each(function(i) {
                shipfrom = $(this).val();
                if (!shipfrom) {
                check = false;
                msg = "You Must Select Ship From Site!";
                }
    
            });
    
            if (check === true) {
                if (confirm('Are you sure to submit?')) {
                    $.ajax({
                        url: "{{route('allocate-save')}}",
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
                            //window.location.href = "{{route('home')}}";
        
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.log(jqXHR.responseText);
                            console.log(textStatus);
                            alert(textStatus + jqXHR.responseText);
                            //location.reload();
                        }
                    });
                }
            } else {
                alert(msg);
                event.preventDefault();
            }
        });
        
    });
</script>

@endsection