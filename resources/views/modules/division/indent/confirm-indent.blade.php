@extends('layouts.master')

@section('content-title')
Confirm Indent
@endsection

@section('content-body')
<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-8">
                <!-- Default box -->
                <div class="card">
                    <div class="card-header">
                        <h5>Confirmation Screen</h5>
                    </div>
                    <form class="form-horizontal" name="requestForm" id="requestForm">
                        @csrf
                        <!-- {{ csrf_field() }} -->
                        <div class="card-body">

                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label class="control-label">Source Site</label>
                                        <input type="text" name="txt_shipfrom" class="form-control"
                                            value="{{$post_data['txt_shipfrom']}}" readonly />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label class="control-label">Product Line</label>
                                        <input type="text" name="txt_prodline" class="form-control"
                                            value="{{$post_data['txt_prodline']}}" readonly />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4">
                                    <div class="form-group">
                                        <label class="control-label">Required Date</label>
                                        <input type="date" name="txt_date" class="form-control"
                                            value="{{$post_data['txt_date']}}" readonly />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                    <div class="table-responsive-md">
                                        <table id="myTable" width="100%"
                                            class="table table-sm table-hover table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Item Code</th>
                                                    <th>Item Description</th>
                                                    <th>Qty Requested (PCs)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $i=0;
                                                $total_qty = $post_data['ind_qty'];
                                                $total_item = $post_data['txt_item'];
                                                @endphp
                                                @foreach ($total_qty as $index=>$input_qty)
                                                @if ($input_qty != NULL)
                                                @php
                                                $i++;
                                                @endphp
                                                <tr>
                                                    <input type="hidden" name="txt_item[{{$i}}]"
                                                        value="{{$total_item[$index]}}" />
                                                    <td>{{$total_item[$index]}}</td>
                                                    <td>{{$total_item[$index]}}</td>
                                                    <td><input type="text" name="txt_qty[{{$i}}]" value="{{$input_qty}}"
                                                            readonly /></td>
                                                </tr>
                                                @endif
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
                        <button type="button" class="btn btn-danger float-left"
                            onclick="window.history.back(); return false;">
                            <i class="fas fa-times-circle"></i> Edit
                        </button>
                        <button type="button" class="btn btn-success float-right subbtn " name="subbtn">
                            <i class="fa fa-check-square"></i> Confirm & Save
                        </button>
                    </div>
                    <!-- /.card-footer-->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row-->
    </div>
    <!-- /.container-fluid -->
</div>
<!-- /.content -->
@endsection

@section('page-script')
<script type="text/javascript">
    $(document).ready(function() {
            // Swal.fire(
            // 'The Internet?',
            // 'That thing is still around?',
            // 'question'
            // )
        $("#loading-div-background").css({
            opacity: 1
        });
        $('.subbtn').click(function(event) {
            $('.subbtn').attr("disabled", true);
            if (confirm('Are you sure to raise this indent?')) {
                $.ajax({
                    url: "{{ url('/division/indent/save') }}",
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
                        Swal.fire({
                            popup: 'swal2-show',
                            icon: 'success',
                            title: data.msg,
                            showConfirmButton: false,
                            closeOnConfirm: false,
                            timer: 2000
                        }).then(function(){
                            window.location = '{{ route('home')}}';
                        });

                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(jqXHR.responseText);
                        alert(textStatus + jqXHR.responseText);
                        location.reload();
                    }
    
                });
            }
        });
    });
</script>

@endsection