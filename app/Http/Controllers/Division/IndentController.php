<?php

namespace ITLLiveWeb\Http\Controllers\Division;

use Illuminate\Http\Request;
use ITLLiveWeb\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class IndentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list(Request $request)
    {
        $prod_line = $request->get('prodline');

        $items =  DB::table('pt_mstr')
            ->select(
                DB::raw('pt_part, pt_desc')
            )
            ->where('pt_status', '=', 'active')
            ->where('pt_prod_line', '=', $prod_line)
            ->get();
        
        $sites = $this->get_indent_sites($prod_line);

        return view('modules.division.indent.raise-indent')
                ->with('items', $items)
                ->with('sites', $sites);
    }

    public function get_indent_sites($prod_line)
    {
        $depots =  DB::table('si_mstr')
            ->select(
                DB::raw('si_site as si_code, si_desc')
            )
            ->where('si_status', '=', "active")
            ->where('si_division', '=', Auth::user()->user_name)
            ->where('si_type', '=', "depot");

        $sites = DB::table('si_mstr')
            ->select(
                DB::raw('si_division AS si_code'),
                DB::raw('( SELECT DISTINCT(cmbuff.si_desc) FROM si_mstr cmbuff WHERE ( cmbuff.si_site = si_mstr.si_division ) ) AS si_desc')
            )
            ->distinct()
            ->where('si_status', '=', "active")
            ->whereNotIn('si_division', [Auth::user()->user_name, "DP990"])
            ->union($depots)
            ->get()
            ->toArray(); //result will be an array
        //dd($sites);

        /* to remove the sites based on prodline creating an array with site code as index*/
        foreach ($sites as $key => $value) {
            $result[$value->si_code]['si_code'] = $value->si_code;
            $result[$value->si_code]['si_desc'] = $value->si_desc;
        }
        if ($prod_line=='tea') {
            unset($result['A0006']);
            unset($result['A0007']);
        } elseif ($prod_line=='food') {
            unset($result['A0004']);
            unset($result['A0005']);
            unset($result['A0007']);
        } elseif ($prod_line=='agro') {
            unset($result['A0004']);
            unset($result['A0005']);
            unset($result['A0006']);
        }
        //dd($result);
        return $result;
    }

    public function confirm(Request $request)
    {
        $data = $request->all(); //This will give the data of all post value
        //dd($data);
        return view('modules.division.indent.confirm-indent')->with('post_data', $data);
    }

    public function get_indent_number($user)
    {
        $msg = "";
        //$get_serial = DB::select('SELECT si_ind_prefix, si_ind_serial, si_division FROM si_mstr where si_site =?', [Auth::user()->user_name]);
        $get_serial =  DB::table('si_mstr')
                        ->select(
                            DB::raw('si_ind_prefix, si_ind_serial, si_division')
                        )
                        ->where('si_site', '=', $user)
                        ->first();

        if ($get_serial) {
            $prefix      = $get_serial->si_ind_prefix;
            $code_serial = $get_serial->si_ind_serial;

            if (!$prefix) {
                $msg = "Prefix Is Not Set. Please Contact With MDM";
                $ind_nbr = null;
            }
            if (!$code_serial) {
                $msg = "Code Serial Is Not Set. Please Contact With MDM";
                $ind_nbr = null;
            } else {
                $msg = '';
                $serial = sprintf('%04d', $code_serial);
                $ind_nbr = $prefix . $serial;
            }
        } else {
            $msg = 'Query Error';
            $ind_nbr = null;
        }

        return compact('msg', 'ind_nbr');
    }

    public function save(Request $request)
    {
        $user         = Auth::user()->user_name;
        $required_date= $request->post('txt_date');
        $prod_line    = $request->post('txt_prodline');
        $shipfrom     = $request->post('txt_shipfrom');

        $item_array   = $request->post('txt_item');
        $qty_array    =  $request->post('txt_qty');

        $date         = Carbon::now()->toDateString();
        $date_created = Carbon::now()->toDateTimeString();
        $msg = '';
        $serial = 0;
        $line_error = false;
        $line_error_msg = '';
        $error = false;
        $error_msg = '';

        
        $ind_nbr_array = $this->get_indent_number($user);
        $ind_nbr = $ind_nbr_array['ind_nbr'];
        $ind_nbr_msg = $ind_nbr_array['msg'];

        if ($ind_nbr == '' || $ind_nbr == null) {
            $error = true;
            $error_msg  = $error_msg. $ind_nbr_msg;
        }
        if (empty($shipfrom)) {
            $error = true;
            $error_msg  = $error_msg. 'No source site Found! ';
        }
        if (empty($user)) {
            $error = true;
            $error_msg  = $error_msg.'Invalid User! ';
        }

        if ($error == false) {
            $transactions_ok = false;

            DB::beginTransaction();
            
            try {
                $insert_header = DB::table('ind_mstr')
                            ->insert([
                                'ind_nbr' => $ind_nbr,
                                'ind_date' => $date,
                                'ind_req_date' => $required_date,
                                'ind_prod_line' => $prod_line,
                                'ind_shipfrom' => $shipfrom,
                                'ind_shipto' => Auth::user()->user_name,
                                'ind_remarks' => "Division Indent",
                                'ind_created' => $date_created,
                                'ind_status' => 'PENDING',
                            ]);

                foreach ($qty_array as $index => $qty) {
                    $serial++;
                    $line = sprintf('%02d', $serial);
                    $req_nbr = $ind_nbr . $line;
                    $item = $item_array[$index];
                    $shipto = Auth::user()->user_name;

                    try {
                        $insert_line = DB::table('indd_det')
                            ->insert([
                                'indd_nbr'     => $ind_nbr,
                                'indd_line'    => $line,
                                'indd_req_nbr' => $req_nbr,
                                'indd_part'    => $item,
                                'indd_shipfrom'=> $shipfrom,
                                'indd_shipto'  => Auth::user()->user_name,
                                'indd_qty_req' => $qty,
                                'indd_status'  => 'PENDING',
                                'indd_created' => $date_created,
                                
                            ]);
                        $transactions_ok = true;
                    } catch (\Exception $e) {
                        $transactions_ok = false;
                        $line_error = true;
                        $line_error_msg = "Error in line# " . $line . ". Error during posting transaction.".$e->getMessage();
                    }
                }
            } catch (\Exception $e) {
                $line_error = true;
                $line_error_msg = "Error in inserting mstr. ".$e->getMessage();
            }

            if ($transactions_ok == true && $line_error == false) {
                $update_serial =  DB::table('si_mstr')
                            ->where('si_site', Auth::user()->user_name)
                            ->increment('si_ind_serial', 1);

                DB::commit();
                return response()->json(['msg'=>'Indent "' . $ind_nbr . '" Has Been Created']);
            } else {
                DB::rollback();
                return response()->json(['msg'=>$line_error_msg]);
            }
        } else {
            return response()->json(['alert'=>'error', 'msg'=> $error_msg]);
        }
    }
}
