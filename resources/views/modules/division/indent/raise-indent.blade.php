@extends('layouts.master')

@section('content-title')
    Raise Indent
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
                            <form class="form-inline ml-3">
                                <div class="input-group input-group">
                                    <select name="prodline" id="prodline" class="form-control form-control-navbar">
                                        <option value="">Please Select A Product Line</option>
                                        <option value="tea" @if (app('request')->get('prodline') == 'tea')
                                            selected="selected" @endif>
                                            Tea
                                        </option>
                                        <option value="food" @if (app('request')->get('prodline') == 'food')
                                            selected="selected" @endif>
                                            Food
                                        </option>
                                        <option value="agro" @if (app('request')->get('prodline') == 'agro')
                                            selected="selected" @endif>
                                            Agro
                                        </option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        {{-- app('request')->get('parameter') instead of $_GET to find url parameter --}}
                        @if (app('request')->get('prodline'))

                        <form class="form-horizontal" name="requestForm" id="requestForm" method="POST" action="{{ route('confirm-indent') }}">
                            @csrf
                            <!-- {{ csrf_field() }} -->
                            <div class="card-body">
                                <input type="hidden" name="txt_prodline" id="txt_prodline" value="{{app('request')->get('prodline')}}">

                                <div class="form-group row">
                                    <label for="txt_shipfrom" class="col-form-label">Source</label>
                                    <div class="col-md-3 col-lg-4">
                                        <select name="txt_shipfrom" id="txt_shipfrom" class="form-control">
                                            <option value="">Please Select A Source</option>
                                            @foreach ($sites as $site)
                                                <option value="{{$site['si_code']}}">{{$site['si_desc']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <label for="txt_date" class="col-form-label">Required Date</label>
                                    <div class="col-md-3 col-lg-4">
                                        <input type="text" name="txt_date" id="txt_date" class="form-control"
                                            value="{{Carbon\Carbon::yesterday()->format('Y-m-d')}}" />
                                    </div>
                                    <div class="col-md-3 col-lg-2">
                                        <button type="button" class="btn btn-block btn-secondary btn-success subbtn " name="subbtn">
                                            <i class="fa fa-check"></i> Confirm
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-md-12 col-lg-12">
                                        <div class="table-responsive-md">
                                            <table id="myTable" width="100%"
                                                class="table table-sm table-hover table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Item Code</th>
                                                        <th>Item Description</th>
                                                        <th>Qty Available</th>
                                                        <th>Qty Requested (PCs)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php
                                                    $i=0;
                                                    @endphp
                                                    @foreach ($items as $item)
                                                    @php
                                                        $i++;
                                                        $qty = get_qty_by_site($item->pt_part,\Auth::user()->user_name,'LG001');
                                                        
                                                        if(($qty->qty_avail)!=NULL){
                                                            $avail_qty = $qty->qty_avail;
                                                        }
                                                        else{
                                                            $avail_qty = 0;
                                                        }
                                                    @endphp
                                                    <tr class="row_data">
                                                        <input type="hidden" name="txt_item[{{$i}}]" id="txt_item" value={{$item->pt_part}}>
                                                        <td>{{$item->pt_part}}</td>
                                                        <td>{{$item->pt_desc}}</td>
                                                        <td>{{$avail_qty}}</td>
                                                        <td><input name="ind_qty[{{$i}}]" id="ind_qty" type="number"
                                                                min=0 max="999999" class="ind_qty form-control"
                                                                style="width: 12em"></td>
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
                        <div class="card-footer">
                            <button type="button" class="btn btn-success float-right subbtn " name="subbtn">
                                <i class="fa fa-check"></i> Confirm & Save
                            </button>
                        </div>
                        <!-- /.card-footer-->
                        @endif
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
    $(document).ready(function (){
        //input validation
        $(".ind_qty ").on("keypress keyup blur", function(event) {
        $(this).val($(this).val().replace(/[^\d].+/, ""));
        if ((event.which < 48 || event.which> 57)) {
            event.preventDefault();
            }
            $(this).css("color", "OrangeRed");
            });

        $('.subbtn').click(function () {
            var check = true;
            var sum = 0;
            var msg = "";
            
            $('.row_data #ind_qty').each(function(i) {
            //alert($(this).val());
                if ($(this).val()) {
                    sum += +$(this).val();
                }
            });

            if (sum === 0) {
                check = false;
                msg = "Qty can not be empty!";
            }
            
            if ($('#txt_shipfrom').val() === "") {
                check = false;
                msg = 'Please Select Source Site!';
            }
            
            if ($('#txt_date').val() === "") {
                check = false;
                msg = 'Please Enter A Required Date!';
            }

            if (check === true) {
                //alert('submitting..');
                $("#requestForm").submit();
                
            } else {
                event.preventDefault();
                alert(msg);
            }

        });
    });
</script>
@endsection