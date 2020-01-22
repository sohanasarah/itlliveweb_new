@extends('layouts.master')
@section('content-title')
Indent View
@endsection
@section('content-body')
<div class="col-md-12">
    <div class="card">
        <div class="card-header">Indent View</div>
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
                            $site = get_site_desc($indd->indd_shipfrom);
                            $shipfrom_desc = $site->si_desc;
                            $i++;
                            @endphp

                            <tr class="row_data">
                                <td>{{$indd->indd_line}}</td>
                                <td>{{$indd->indd_req_nbr}}</td>
                                <td>{{$indd->indd_part}}</td>
                                <td>{{$indd->pt_desc}}</td>
                                <td>{{$shipto_desc}}</td>
                                <td>{{$shipfrom_desc}}</td>
                                <td>{{$indd->indd_qty_req}}</td>
                                <td>{{ucfirst(strtolower($indd->indd_status))}}</td>
                                <td>
                                    @if ($ind_mstr->ind_cancelable == 'true')
                                    <button type="button" class="btn btn-danger"
                                        onclick="close_indent('{{ $indd->indd_nbr}} ', '{{ $indd->indd_id}}')">
                                        <i class="fa fa-trash-alt"></i> Close
                                    </button>
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
                        onclick="close_indent('{{$ind_mstr->ind_nbr}}', '')">
                        <i class="fa fa-trash-alt"></i>
                        Close Indent
                    </button>
                </div>
                @endif
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
                url: "{{ url('/division/stn/close/{ind_nbr}/{ind_id}') }}",
                type: "GET",
                data: {
                    ind_nbr: nbr,
                    ind_id: id
                },
                success: function(data) {
                    console.log(data);
                    alert(data.msg);
                    //window.location.href = 'index.php?mod=stn&act=list';
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    alert(textStatus);
                    console.log(jqXHR.responseText);
                    //location.reload();
                }
            });
        }
    }
    
   
</script>

@endsection