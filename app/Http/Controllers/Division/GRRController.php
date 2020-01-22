<?php

namespace ITLLiveWeb\Http\Controllers\division;

use Illuminate\Http\Request;
use ITLLiveWeb\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GRRController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function grr_list(Request $request)
    {
        $do_ships = $this->get_open_doship($request->get('start_date'), $request->get('end_date'));
        return view('modules.division.grr.grr-list')
                    ->with('do_ships', $do_ships);
    }

    public function get_open_doship($startDate, $endDate)
    {
        $user = Auth::user()->user_name;
        $do_ships = DB::table('ds_mstr')
                    ->select(
                        DB::raw('ds_donbr, ds_ind_nbr, ds_prod_line, ds_ship_date,  
                                ds_ship_from, ds_ship_to, sum(dsd_qty_ship) as qty_ship')
                    )
                    ->leftJoin('dsd_det', 'dsd_det.dsd_donbr', '=', 'ds_mstr.ds_donbr')
                    ->leftJoin('pt_mstr', 'pt_mstr.pt_part', '=', 'dsd_det.dsd_part')
                    ->where('ds_ship_to', $user)
                    ->where('ds_status', "open")
                    ->where(function ($query) use ($startDate, $endDate) {
                        if ($endDate !="" && $startDate !="") {
                            $query->whereBetween('ds_ship_date', [$startDate, $endDate]);
                        }
                        return $query;
                    })
                    ->groupBy('ds_donbr')
                    ->orderBy('ds_donbr', 'desc')
                    ->orderBy('ds_ship_date', 'asc')
                    ->get();

        //dd($do_ships);
        return $do_ships;
    }

    public function grr_view(Request $request)
    {
        $do_nbr = $request->route('do_nbr'); //route id

        $ds_mstr = DB::table('ds_mstr')
                ->select(
                    DB::raw('*')
                )
                ->where('ds_donbr', $do_nbr)
                ->first();

        $dsd_det = DB::table('dsd_det')
                ->select(
                    DB::raw('*')
                )
                ->leftJoin('pt_mstr', 'pt_mstr.pt_part', '=', 'dsd_det.dsd_part')
                ->where('dsd_donbr', $do_nbr)
                ->get();

        return view('modules.division.grr.grr-view')
                ->with('ds_mstr', $ds_mstr)
                ->with('dsd_det', $dsd_det);
    }

    public function grr_save(Request $request)
    {
        $do_nbr         = $request->post('do_nbr');

        $receipt_date   = Carbon::now()->toDateString();
        $date_created   = Carbon::now()->toDateTimeString();
        $user           = Auth::user()->user_name;
        $good_loc       = "LG001";
        $error          = false;

        /*****array input*****/
        $dsd_id_array   = $request->post('do_id');
        $qty_good_array = $request->post('qty_good');

        $line = 0;
        $line_error = false;
        $line_error_msg = "";
        
        $qty_good = 0;
        $qty_short = 0;
        $qty_damaged = 0;
        $qty_receipt = 0; /* total receipt qty for an item */

        $tr_qty_good = 0;
        $tr_qty_short = 0;
        $tr_qty_damaged = 0;


        if (empty($user)) {
            $error = true;
            $error_msg = 'Invalid Site! ';
        }
        if (empty($do_nbr)) {
            $error = true;
            $error_msg ='Invalid DO Number! ';
        }
        if (empty($dsd_id_array)) {
            $error = true;
            $error_msg ='NO ID FOUND! ';
        }

        if ($error == false) {
            DB::beginTransaction();

            foreach ($dsd_id_array as $index => $dsd_id) {
                $line++;
                $do_details = $this->get_do_details_by_id($dsd_id);

                if (!$do_details) {
                    $line_error = true;
                    $line_error_msg = "No STN record found for [dsd id# " . $dsd_id . "]!!! Cannot proceed.";
                    break;
                } else {
                    $req_nbr         = $do_details->dsd_req_nbr;
                    $item            = $do_details->dsd_part;
                    $lot             = $do_details->dsd_lot;
                    $transport_loc   = $do_details->ds_transporter;
                    $qty_shipped     = $do_details->dsd_qty_ship;
                    $qty_good        = $qty_good_array[$index];
                    $qty_receipt     = $qty_good;
            
                    if ($qty_receipt != $qty_shipped) {
                        $line_error = true;
                        $line_error_msg = "Receipt qty(" . $qty_receipt . ") is not equal to Shipped qty(" . $qty_shipped . ")";
                    } else {
                        try {
                            $transactions_ok = false;

                            $insert =  DB::table('dsr_det')->insert([
                                            'dsr_donbr'      => $do_nbr,
                                            'dsr_req_nbr'    => $req_nbr,
                                            'dsr_part'       => $item,
                                            'dsr_lot'        => $lot,
                                            'dsr_date_rct'   => $receipt_date,
                                            'dsr_qty_rct'    => $qty_receipt,
                                            'dsr_qty_good'   => $qty_good,
                                            'dsr_qty_short'  => $qty_short,
                                            'dsr_qty_damaged'=> $qty_damaged,
                                            'dsr_posted'     => "false"
                                        ]);
                        
                            if ($insert) {
                                $reference   = $req_nbr;
                                $tr_site     = $user;
                                $tr_qty_good = $qty_good;

                                if ($tr_qty_good > 0) {
                                    $ISS_GIT= stock_transaction($do_nbr, $receipt_date, "ISS-GIT", $tr_site, $transport_loc, $tr_site, $tr_site, $item, $lot, ($tr_qty_good * -1), $reference, "GRR/WEB/GOOD", $user);
                                    $RCT_DO = stock_transaction($do_nbr, $receipt_date, "RCT-DO", $tr_site, $good_loc, $tr_site, $tr_site, $item, $lot, $tr_qty_good, $reference, "GRR/WEB/GOOD", $user);
                                
                                    if ($ISS_GIT && $RCT_DO) {
                                        $transactions_ok = true;
                                    } else {
                                        $transactions_ok = false;
                                        $line_error = true;
                                        $line_error_msg = "Error in line# " . $line . ". Error during stock transaction.".$e->getMessage();
                                    }
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
            }
            
            if (!$line_error && $transactions_ok) {
                $update_dsmstr =  DB::table('ds_mstr')
                            ->where('ds_donbr', $do_nbr)
                            ->update([
                                'ds_rct_date' => $receipt_date,
                                'ds_status'   => 'closed',
                                'ds_donbr'    => $do_nbr
                            ]);
                
                DB::commit();

                $success_msg = 'GRR done! DO "' . $do_nbr . '" has been received!!';
                return response()->json(['msg'=>$line_error.$transactions_ok.$success_msg]);
            } else {
                DB::rollback();

                return response()->json(['alert'=>'error', 'msg'=> $line_error_msg]);
            }
        } else {
            return response()->json(['alert'=>'error', 'msg'=> $error_msg]);
        }
    }
    public function get_do_details_by_id($id)
    {
        $dsd_det = DB::table('dsd_det')
                ->select(
                    DB::raw('*')
                )
                ->leftJoin('ds_mstr', 'ds_mstr.ds_donbr', '=', 'dsd_det.dsd_donbr')
                ->where('dsd_id', $id)
                ->first();

        return $dsd_det;
    }
}
