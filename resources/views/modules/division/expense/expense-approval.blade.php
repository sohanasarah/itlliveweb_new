@extends('layouts.master')

@section('content-title')
Expense Approval
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
                            <h3 class="card-title">
                                filter by depot
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            @php
                            $i = 0;
                            @endphp
                            <form role="form" id="requestForm">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Depot</th>
                                                <th>Date</th>
                                                <th>Expense Head</th>
                                                <th>GL Code</th>
                                                <th>Project Name</th>
                                                <th>Project Code</th>
                                                <th>Expense Amount</th>
                                                <th>Approved Amount</th>
                                                <th>Select</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($expenses as $depot_exp)
                                            @php

                                            $i++;
                                            $prj_details = Helper::get_project_desc($depot_exp->exp_project);
                                            if($prj_details){
                                            $prj_desc= $prj_details->prj_desc;
                                            }
                                            else{
                                            $prj_desc= 'N/A';
                                            }
                                            @endphp
                                            <input type="hidden" name="exp_id[{{$i}}]" value="{{$depot_exp->exp_id}}" />
                                            {{-- <input type="hidden" name="exp_depot[{{$i}}]" class="exp_depot"
                                            value="{{$depot_exp->exp_depot}}"> --}}
                                            <input type="hidden" name="exp_amount[{{$i}}]" class="exp_amount"
                                                value="{{$depot_exp->exp_amount}}" />
                                            {{-- <input type="hidden" name="exp_date[{{$i}}]"
                                            value="{{$depot_exp->exp_date}}" /> --}}
                                            <tr>
                                                <td>{{$depot_exp->si_desc}}</td>
                                                <td>{{$depot_exp->exp_date}}</td>
                                                <td>{{$depot_exp->gl_description}}</td>
                                                <td>{{$depot_exp->exp_account}}</td>
                                                <td>{{$prj_desc}}</td>
                                                <td>{{$depot_exp->exp_project}}</td>
                                                <td>{{$depot_exp->exp_amount}}</td>
                                                <td><input type="number" style="width: 7em" name="apprv_amount[{{$i}}]"
                                                        class="apprv_amount" value="{{ $depot_exp->exp_amount }}"></td>
                                                <td><input type="checkbox" name="checked[{{$i}}]" class="checked"
                                                        value="{{$depot_exp->exp_id}}">
                                                </td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td style="color: orangered; font-weight:bold">Total Amount</td>
                                                <td><input type="number" name="total" class="total"
                                                        style="width: 7em; color:red; font-weight:bold" readonly></td>
                                                <td></td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                        <div class="card-footer">
                            <button type="button" class="btn btn-secondary float-right subbtn " name="subbtn">
                                <i class="fa fa-check"></i> Approve Selected
                            </button>
                        </div>
                        <!-- /.card-footer-->
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function validateAmount() {
        var a1 = $(".apprv_amount");
        var a2 = $(".exp_amount");
       
        //alert(a1.length);
        var flag = 0;
        
        for (var i = 0; i < a1.length; i++) { 
            if (a1[i].value - a2[i].value > 0) {
                alert('Approved amount can not be greater than Expense amount!');
                flag = flag++;
                return false;
            }
        }
        
        if (flag === 0) {
            return true;
        } else {
            return false;
        }
    }
    // Does work
    function calcTotal() {
        var sum = 0;
        $(".apprv_amount").each(function () {
            sum += +$(this).val();
        });
        $(".total").val(sum);
    }

    $(document).ready(function () {        
        calcTotal();
        
        $(document).on("change", ".apprv_amount", function () {
            var sum = 0;
            $(".apprv_amount").each(function () {
                sum += +$(this).val();
            });
            $(".total").val(sum);
        
        });
        
        $('.subbtn').click(function () {
            if (validateAmount()) {
                var check = false;
                $(".checked").each(function (i) {
                    if ($(this).is(":checked")) {
                        check = true;   
                    }   
                });
            
                if (check === true) {
                    $('.subbtn').attr("disabled", true);
                    if (confirm('Are you sure to proceed?')) {
                        $.ajax({
                            url: "{{ url('/division/expense-approval/save') }}",
                            type: "POST",
                            data: $('#requestForm').serialize(),
                            //dataType: 'json',
                            statusCode: {404: function () {
                                alert('page not found');
                            }},
                            success: function (data) {
                                console.log(data);
                                alert(data.msg);
                                location.reload();
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                alert(textStatus + jqXHR.responseText);
                                location.reload();
                            }
                        });
                    }
                }
                else {
                    alert("Please Select One!");
                    event.preventDefault();
                }
            }
        });
    });
</script>
@endsection