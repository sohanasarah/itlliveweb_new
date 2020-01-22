<?php

namespace ITLLiveWeb\Http\Controllers\Division;

use Illuminate\Http\Request;
use ITLLiveWeb\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class STNController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function open_indent_list(Request $request)
    {
        $depots  = $this->get_depots();
        $indents = $this->get_open_indent_details($request->get('depot'));
        return view('modules.division.stn.open-indents')
        ->with('depots', $depots)
        ->with('indents', $indents);
    }

    public function get_depots()
    {
        $depots =  DB::table('si_mstr')
            ->select(
                DB::raw('si_site, si_desc')
            )
            ->where('si_status', '=', "active")
            ->where('si_division', '=', Auth::user()->user_name)
            ->where('si_type', '=', "depot")
            ->get();
        // dd($depots);
        return $depots;
    }

    public function get_open_indent_details($request)
    {
        // $depotFilter = $request->get('depot');
        $division = Auth::user()->user_name;
        
        $depots =  DB::table('si_mstr')
            ->select(
                DB::raw('si_site')
            )
            ->where('si_status', '=', "active")
            ->where('si_division', '=', $division)
            ->where('si_type', '=', "depot")
            ->pluck('si_site');

        $indents = DB::table('ind_mstr')
                ->select(
                    DB::raw('ind_nbr, ind_date, ind_req_date, ind_prod_line, ind_shipfrom, 
                             ind_shipto, count(indd_id) as items, sum(indd_qty_req) as qty_pc,
                             sum(indd_qty_req * pt_um_conv) as qty_kg, 
                             sum(indd_qty_ship) as qty_ship, ind_status, ind_created')
                )
                ->leftJoin('indd_det', 'indd_det.indd_nbr', '=', 'ind_mstr.ind_nbr')
                ->leftJoin('pt_mstr', 'pt_mstr.pt_part', '=', 'indd_det.indd_part')
                ->whereNotIn('ind_status', ["CLOSED", "CANCELLED"])
                ->when($request, function ($query) use ($request) {
                    return $query->where('ind_shipto', $request);
                }, function ($query) use ($depots,$division) {         //IF FALSE
                    return $query->where(function ($query) use ($depots,$division) {
                        return $query->whereIn('ind_shipto', $depots)
                                    ->orWhere('ind_shipfrom', $division)
                                    ->orWhereIn('ind_shipfrom', $depots);
                    });
                })
                    ->groupBy('ind_nbr')
                    ->orderBy('ind_created', 'desc')
                    // ->toSql();
                    ->get();

        //dd($indents);
        return $indents;
    }

    public function allocate_list(Request $request)
    {
        $ind_nbr = $request->route('ind_nbr'); //route id
        $user =  Auth::user()->user_name;

        $ind_mstr = DB::table('ind_mstr')
                ->select(
                    DB::raw('ind_nbr, ind_date, ind_shipto, ind_status, ind_cancelable, ind_prod_line')
                )
                ->where('ind_nbr', $ind_nbr)
                ->first();
        
        $ind_details = DB::table('indd_det')
                ->select(
                    DB::raw('indd_id, indd_nbr, indd_line, indd_req_nbr, indd_part, pt_desc,
                             indd_qty_req, indd_status, indd_shipfrom, indd_shipto')
                )
                ->leftJoin('pt_mstr', 'pt_mstr.pt_part', '=', 'indd_det.indd_part')
                ->where('indd_nbr', $ind_nbr)
                ->whereIn('indd_status', ['PENDING','ALLOCATED'])
                ->get();

        $ind_shipto = $ind_mstr->ind_shipto;
        $source_sites = DB::table('si_mstr')
            ->select(
                DB::raw('si_site, si_desc')
            )
            ->where('si_status', '=', "active")
            ->where(function ($query) use ($user,$ind_shipto) {
                $query->where('si_division', '=', $user)
                      ->where('si_site', '<>', $ind_shipto);
            })
            ->orWhere(function ($query) use ($user) {
                $query->where('si_type', '=', 'factory')
                      ->orWhere('si_type', '=', 'warehouse');
            })
            ->get();

        return view('modules.division.stn.allocate-screen')
                ->with('ind_mstr', $ind_mstr)
                ->with('ind_details', $ind_details)
                ->with('sources', $source_sites);
    }

    public function get_indent_number($site)
    {
        $msg = "";
        $get_serial =  DB::table('si_mstr')
                        ->select(
                            DB::raw('si_ind_prefix, si_ind_serial, si_division ')
                        )
                        ->where('si_site', '=', $site)
                        ->first();

        if ($get_serial) {
            $prefix      = $get_serial->si_ind_prefix;
            $code_serial = $get_serial->si_ind_serial;

            if (!$prefix) {
                return response()->json(['msg'=>"Prefix Is Not Set. Please Contact With MDM"]);
            }
            if (!$code_serial) {
                return response()->json(['msg'=>"Code Serial Is Not Set. Please Contact With MDM"]);
            } else {
                $serial = sprintf('%04d', $code_serial);
                $ind_nbr = $prefix . $serial;
                return $ind_nbr;
            }
        } else {
            return null;
        }
    }

    public function allocate_save(Request $request)
    {
        $ind_nbr        = $request->post('ind_nbr');
        $prod_line      = $request->post('prod_line');
        $shipto     = $request->post('ship_to');

        $ind_id_array   = $request->post('ind_id');
        $qty_array      = $request->post('ind_qty');
        $shipfrom_array = $request->post('ship_from');

        $date           = Carbon::now()->toDateString();
        $date_created   = Carbon::now()->toDateTimeString();
        $required_date  = Carbon::tomorrow()->toDateString();

        $user = Auth::user()->user_name;

        $msg = '';
        $line = 0;
        $new_line = 0;
        $line_error = false;
        $line_error_msg = "";
        $success_msg = "";
        $previous_shipfrom = "";

        DB::beginTransaction();

        foreach ($ind_id_array as $index => $indd_id) {
            $line++;
            $shipfrom = $shipfrom_array[$index];
            $ind_qty = $qty_array[$index];

            //IF DIVISION ALLOCATES TO DIFFERENT SOURCE
            // A new record in master table will be created. New indent number will be generated.
            
            if ($shipfrom != $user) {
                try {
                    //if multiple lines have same shipfrom site than only one header will be created.
                    if ($shipfrom != $previous_shipfrom) {
                    
                        //Generate a new indent number
                        $new_ind_nbr = $this->get_indent_number($shipto);

                        $insert_header = DB::table('ind_mstr')
                            ->insert([
                                'ind_nbr'       => $new_ind_nbr,
                                'ind_date'      => $date,
                                'ind_req_date'  => $required_date,
                                'ind_prod_line' => $prod_line,
                                'ind_shipfrom'  => $shipfrom,
                                'ind_shipto'    => $shipto,
                                'ind_remarks'   => "Division Indent(Source Changed)",
                                'ind_created'   => $date_created,
                                'ind_status'    => "ALLOCATED",
                            ]);

                        $update_serial =  DB::table('si_mstr')
                                        ->where('si_site', $shipto)
                                        ->increment('si_ind_serial', 1);
                    }

                    $new_line++;
                    $new_ind_line = sprintf('%02d', $new_line);
                    $new_req_nbr = $new_ind_nbr . $new_ind_line;

                    $update_line = DB::table('indd_det')
                                        ->where('indd_id', '=', $indd_id)
                                        ->update([
                                            'indd_nbr'     => $new_ind_nbr,
                                            'indd_line'    => $new_ind_line,
                                            'indd_req_nbr' => $new_req_nbr,
                                            'indd_shipfrom'=> $shipfrom,
                                            'indd_qty_req' => $ind_qty,
                                            'indd_remarks' => $ind_nbr,
                                            'indd_status'  => 'ALLOCATED',
                                            'indd_allocated' => $date_created,
                                        ]);

                    //finally commit all
                    DB::commit();
                       
                    $success_msg =  $success_msg. 'New Indent ' . $new_ind_nbr . ' Has Been Created!';
                } catch (\Exception $e) {
                    DB::rollback();

                    $line_error = true;
                    $line_error_msg = "Error in line# " . $line . $e->getMessage();
                }

                $previous_shipfrom = $shipfrom;
            }
            //IF DIVISION ALLOCATES TO ITSELF

            else {
                try {
                    $update_line = DB::table('indd_det')
                            ->where('indd_id', '=', $indd_id)
                            ->update([
                                'indd_shipfrom'=> $shipfrom,
                                'indd_qty_req' => $ind_qty,
                                'indd_remarks' => $ind_nbr,
                                'indd_status'  => 'ALLOCATED',
                                'indd_allocated' => $date_created,
                            ]);

                    //finally commit all
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollback();

                    $line_error = true;
                    $line_error_msg = "Error in line# " . $line . $e->getMessage();
                }
            }
        }
        
        if ($line_error == true) {
            return response()->json(['msg'=> $line_error_msg]);
        } else {
            try {
                $update_mstr =  DB::table('ind_mstr')
                            ->where('ind_nbr', $ind_nbr)
                            ->update([
                                'ind_status'     => 'ALLOCATED',
                                'ind_allocated'  => $date_created,
                                'ind_cancelable' => 'false'
                            ]);

                $check_lines =  DB::table('ind_mstr')
                        ->select(
                            DB::raw('*')
                        )
                        ->where('ind_nbr', $ind_nbr)
                        ->get();

                if (!$check_lines->isEmpty()) {
                    $status_close = DB::table('ind_mstr')
                            ->where('ind_nbr', $ind_nbr)
                            ->update([
                                'ind_status'  => 'CLOSED'
                            ]);
                }

                //finally commit all
                DB::commit();

                //return response as json data
                return response()->json(['msg'=>'Indent ' . $ind_nbr . ' Has Been Allocated!!'.$success_msg]);
            } catch (\Exception $e) {
                DB::rollback();

                $error_msg = "Error " . $e->getMessage();
                return response()->json(['msg'=> $error_msg]);
            }
        }
    }

    public function stn_list(Request $request)
    {
        $ind_nbr    = $request->route('ind_nbr');    //route id
        $ind_status = $request->route('ind_status'); //route id
        
        $user =  Auth::user()->user_name;

        $ind_mstr = DB::table('ind_mstr')
                ->select(
                    DB::raw('ind_nbr, ind_date, ind_shipto, ind_status, ind_cancelable, ind_prod_line')
                )
                ->where('ind_nbr', $ind_nbr)
                ->first();
        
        $ind_details = DB::table('indd_det')
                ->select(
                    DB::raw('indd_id, indd_nbr, indd_line, indd_req_nbr, indd_part, pt_desc,
                             indd_status, indd_shipfrom, indd_shipto,
                             indd_qty_req as qty_pc, indd_qty_ship as qty_ship')
                )
                ->leftJoin('pt_mstr', 'pt_mstr.pt_part', '=', 'indd_det.indd_part')
                ->where('indd_nbr', $ind_nbr)
                ->whereIn('indd_status', ['PENDING','ALLOCATED'])
                ->get();

        return view('modules.division.stn.stn-screen')
                ->with('ind_mstr', $ind_mstr)
                ->with('ind_details', $ind_details);
    }

    public function get_qty_by_lot(Request $request)
    {
        //data
        $site = $request->site;
        $item = $request->item;
        $lot = $request->lot;

        $avail_qty =  DB::table('ld_det')
            ->select(
                DB::raw('ld_qty_oh AS qty_avail')
            )
            ->where('ld_part', '=', $item)
            ->where('ld_site', '=', $site)
            ->where('ld_lot', '=', $lot)
            ->where('ld_qty_oh', '<>', 0)
            ->first();


        return response()->json([$avail_qty]);
    }

    public function stn_confirm(Request $request)
    {
        $post_data = $request->all();

        $transports = DB::table('code_mstr')
                ->select(
                    DB::raw('*')
                )
                ->where('code_fldname', 'transport_code')
                ->get();

        $do_nbr = $this->get_do_serial($request->post('ship_to'));

        return view('modules.division.stn.confirm-stn-screen')
                ->with('postData', $post_data)
                ->with('transports', $transports)
                ->with('doNbr', $do_nbr);
    }

    public function get_do_serial($site)
    {
        $do_nbr = "";
        $sel_serial = DB::table('si_mstr')
                ->select(
                    DB::raw('si_do_serial')
                )
                ->where('si_site', $site)
                ->first();

        if ($sel_serial) {
            $code_serial = $sel_serial->si_do_serial;
            $serial = sprintf('%04d', $code_serial);
            $do_nbr = "D" . substr($site, 2, 3) . $serial;
        } else {
            $do_nbr = null;
        }
        return $do_nbr;
    }

    public function stn_save(Request $request)
    {
        $ind_nbr        = $request->post('ind_nbr');
        $prod_line      = $request->post('prod_line');
        $shipfrom       = $request->post('shipfrom');
        $shipto         = $request->post('shipto');
        $transport_loc  = $request->post('transporter');
        $truck_no       = $request->post('truck_no');
        $vat_challan    = $request->post('vat_challan');

        $shipdate       = Carbon::now()->toDateString();
        $date_created   = Carbon::now()->toDateTimeString();
        $user           = Auth::user()->user_name;

        /*****array input*****/
        $ind_id_array   = $request->post('ind_id');
        $item_array     = $request->post('item');
        $lot_array      = $request->post('lot');
        $qty_ship_array = $request->post('qty_ship');

        $msg = '';
        $line = 0;
        $line_error = false;
        $line_error_msg = '';
        $error = false;
        $error_msg = '';

        if (empty($shipfrom)) {
            $error = true;
            $error_msg = $error_msg . 'Invalid Ship From Site! ';
        }

        if (empty($shipto)) {
            $error = true;
            $error_msg = $error_msg . 'Invalid Ship To Site! ';
        }

        if (empty($qty_ship_array)) {
            $error = true;
            $error_msg = $error_msg . 'No Qty Given! ';
        }

        if (empty($item_array)) {
            $error = true;
            $error_msg = $error_msg . 'No Item Selected! ';
        }

        if (empty($lot_array)) {
            $error = true;
            $error_msg = $error_msg . 'No Lot Given! ';
        }

        if ($shipfrom != $user) {
            $error = true;
            $error_msg = $error_msg . 'Wrong Ship From Site! ';
        }

        if ($error == false) {
            $transactions_ok = false;

            DB::beginTransaction();

            $do_nbr = $this->get_do_serial($shipto);

            try {
                $insert_header =  DB::table('ds_mstr')
                            ->insert([
                                'ds_donbr'      => $do_nbr,
                                'ds_ind_nbr'    => $ind_nbr,
                                'ds_prod_line'  => $prod_line,
                                'ds_ship_date'  => $shipdate,
                                'ds_ship_from'  => $shipfrom,
                                'ds_ship_to'    => $shipto,
                                'ds_transporter'=> $transport_loc,
                                'ds_truck_no'   => $truck_no,
                                'ds_vat_challan'=> $vat_challan,
                                'ds_status'     => 'open'
                            ]);

                foreach ($ind_id_array as $index => $indd_id) {
                    $line++;
                    $item     = $item_array[$index];
                    $lot      = $lot_array[$index];
                    $qty_ship = $qty_ship_array[$index];

                    $ind_details = $this->get_indent_details_by_id($indd_id);
                    $indd_nbr = $ind_details->indd_nbr;
                    $req_nbr  = $ind_details->indd_req_nbr;
                    $ind_qty  = $ind_details->indd_qty_req;
                    $open_qty = $ind_details->indd_qty_req - $ind_details->indd_qty_ship;
                    if ($qty_ship != 0) {
                        try {
                            $insert_line = DB::table('dsd_det')
                            ->insert([
                                'dsd_donbr'    => $do_nbr,
                                'dsd_req_nbr'  => $req_nbr,
                                'dsd_part'     => $item,
                                'dsd_lot'      => $lot,
                                'dsd_qty_ship' => $qty_ship,
                                'dsd_date_ship'=> $shipdate,
                                'dsd_created'  => $date_created,
                                'dsd_posted'   => 'false',
                            ]);
                        
                            if ($insert_line) {
                                $update_indent_line =  DB::table('indd_det')
                                                ->where('indd_id', $indd_id)
                                                ->update([
                                                    'indd_qty_ship'  => $qty_ship,
                                                    'indd_status'    => 'CLOSED'
                                                ]);

                                $location    = $this->get_location($shipfrom);
                                $reference   = $req_nbr;
                                $tr_qty_chg  = $qty_ship * (-1);
                                $rct_git_qty = $qty_ship;

                                //stock transaction
                                $ISS_DO  = stock_transaction($do_nbr, $shipdate, "ISS-DO", $shipfrom, $location, $shipto, $shipfrom, $item, $lot, $tr_qty_chg, $reference, "Division STN", $user);
                                $RCT_GIT = stock_transaction($do_nbr, $shipdate, "RCT-GIT", $shipto, $transport_loc, $shipfrom, $shipto, $item, $lot, $rct_git_qty, $reference, "Division STN", $user);
                                
                                if ($ISS_DO && $RCT_GIT) {
                                    $transactions_ok = true;
                                } else {
                                    $transactions_ok = false;
                                    $line_error = true;
                                    $line_error_msg = "Error in line # " . $line . ". Error in updating tr_hist.". $e->getMessage();
                                }
                            } else {
                                $line_error = true;
                                $line_error_msg = "There was some error in query";
                            }
                        } catch (\Exception $e) {
                            $line_error = true;
                            $line_error_msg = "Error in line# " . $line . ". Error during posting transaction.".$e->getMessage();
                        }
                    }
                }
                /* end of foreach */
            } catch (\Exception $e) {
                $line_error = true;
                $line_error_msg = "Error in inserting mstr. ".$e->getMessage();
            }
            /* end of mstr */

            /******Commit or Rollback Block */
            if ($line_error== false && $transactions_ok == true) {
                
                /***Update DO Serial in si_mstr */

                $update_serial =  DB::table('si_mstr')
                            ->where('si_site', $shipto)
                            ->increment('si_do_serial', 1);

                
                /** Check Lines in indd_det. If all are closed then close the indent in ind_mstr */
                
                $total_line = 0;
                $shipped_line = 0;

                $check_lines =  DB::table('indd_det')
                        ->select(
                            DB::raw('indd_status')
                        )
                        ->where('indd_nbr', $ind_nbr)
                        ->get();

                foreach ($check_lines as $key => $value) {
                    $total_line++;
                    if ($value->indd_status == "CLOSED") {
                        $shipped_line++;
                    }
                }

                if ($total_line == $shipped_line) {
                    $status_close = DB::table('ind_mstr')
                            ->where('ind_nbr', $ind_nbr)
                            ->update([
                                'ind_status'  => 'CLOSED'
                            ]);
                }

                /**********FINALLY COMMIT ALL QUERIES *****/
                /******************************** */
                DB::commit();

                /****return success msg to view */

                return response()->json(['alert'=>'success', 'msg'=>'New DO ' . $do_nbr . ' Has Been Created!!']);
            } else {

                /****else ROLLBACK */
                DB::rollback();
                return response()->json(['alert'=>'error','msg'=> $line_error_msg]);
            }
        } else {
            return response()->json(['alert'=>'error', 'msg'=> $error_msg]);
        }
    }

    public function get_indent_details_by_id($id)
    {
        $ind_details = DB::table('indd_det')
                ->select(
                    DB::raw('indd_id, indd_nbr, indd_line, indd_req_nbr, indd_part,
                             indd_qty_req, indd_status, indd_shipfrom, indd_shipto, indd_qty_ship')
                )
                ->where('indd_id', $id)
                ->first();
        
        return $ind_details;
    }

    public function get_location($site)
    {
        $get_location= DB::table('si_mstr')
                    ->select('si_location')
                    ->where('si_site', '=', $site)
                    ->first();

        if ($get_location) {
            $location = $get_location->si_location;
        } else {
            $location = null;
        }
        return $location;
    }

    public function view_indent(Request $request)
    {
        $ind_nbr = $request->route('ind_nbr'); //route id

        $ind_mstr = DB::table('ind_mstr')
                ->select(
                    DB::raw('ind_nbr, ind_date, ind_shipto, ind_status, ind_cancelable, ind_prod_line')
                )
                ->where('ind_nbr', $ind_nbr)
                ->first();
        
        $ind_details = DB::table('indd_det')
                ->select(
                    DB::raw('indd_id, indd_nbr, indd_line, indd_req_nbr, indd_part, pt_desc,
                             indd_qty_req, indd_status, indd_shipfrom, indd_shipto')
                )
                ->leftJoin('pt_mstr', 'pt_mstr.pt_part', '=', 'indd_det.indd_part')
                ->where('indd_nbr', $ind_nbr)
                ->get();


        return view('modules.division.stn.view-indent')
                ->with('ind_mstr', $ind_mstr)
                ->with('ind_details', $ind_details);
    }

    public function close_indent(Request $request)
    {
        $ind_nbr = $request->post('ind_nbr');
        $ind_id = $request->post('ind_id');

        DB::beginTransaction();

        try {

            /* FOR CLOSING SELECTIVE LINES */
            if ($ind_id != "") {
                $close_details = DB::table('indd_det')
                  ->where('indd_nbr', $ind_nbr)
                  ->where('indd_id', $ind_id)
                  ->update([
                      'indd_status' => 'CLOSED'
                  ]);

            // $check_line = DB::table('indd_det')
                //             ->select(
                //                 DB::raw('indd_status')
                //             )
                //             ->where('indd_nbr', $ind_nbr)
                //             ->get();
            
                // if ($res_line) {
                //     while ($row_line = mysqli_fetch_assoc($res_line)) {
                //         $total_line++;
                //         if ($row_line['indd_status'] == "CLOSED") {
                //             $shipped_line++;
                //         }
                //     }
                // }
                // if ($total_line == $shipped_line) {
                //     $query = 'UPDATE ind_mstr SET ind_status = "CLOSED" WHERE ind_nbr="' . $ind_nbr . '"';
                //     mysqli_query($conn, $query);
                // }
            }
            /* FOR CLOSING ALL LINES */
            else {
                $close_header = DB::table('ind_mstr')
                  ->where('ind_nbr', $ind_nbr)
                  ->update([
                      'ind_status' => 'CLOSED'
                  ]);

                $close_details = DB::table('indd_det')
                  ->where('indd_nbr', $ind_nbr)
                  ->update([
                      'indd_status' => 'CLOSED'
                  ]);
            }

            DB::commit();
            return response()->json(['msg'=> 'Indent ('.$ind_nbr.') Has Been Closed!']);
        } catch (\Exception $e) {
            return response()->json(['msg'=> 'Error!'.$e->getMessage()]);
        }
    }
}
