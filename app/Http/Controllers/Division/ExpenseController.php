<?php

namespace App\Http\Controllers\division;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function list()
    {
        $expenses = $this->unapproved_expenses();
        return view('modules.division.expense.expense-approval')->with('expenses', $expenses);
    }

    public function unapproved_expenses()
    {
        $expenses =  DB::table('expenses')
            ->select(
                DB::raw('si_division,si_desc,gl_glcode, gl_description, exp_id, exp_depot, exp_account,
                        exp_date,exp_project,exp_amount,apprv_amount')
            )
            ->leftJoin('gl_mstr', 'gl_mstr.gl_glcode', '=', 'expenses.exp_account')
            ->leftJoin('si_mstr', 'si_mstr.si_site', '=', 'expenses.exp_depot')
            ->where('si_division', '=', Auth::user()->user_name)
            ->where('exp_status', '=', 'unapproved')
            ->get();
        //dd($expenses);
        return $expenses;
    }


    public function save_approval(Request $request)
    {
        $checked_ids = $request->input('checked');
        $apprv_amount = $request->input('apprv_amount');
        $date_approved = Carbon::now()->toDateTimeString();
        $msg = '';
        DB::beginTransaction();
        try {
            foreach ($checked_ids as $index => $exp_id) {
                $amount = $request->input('exp_amount')[$index];
                $update = $result = DB::table('expenses')
                        ->where('exp_id', $exp_id)
                        ->update([
                            'exp_status' => "approved",
                            'apprv_amount' => $apprv_amount[$index] ,
                            'apprv_created' => $date_approved

                        ]);
            }
            DB::commit();
            //$msg = 'Status Has Been Updated';
            return response()->json(['msg'=>'Status Has Been Updated']);
        } catch (\Exception $e) {
            DB::rollback();
            //$msg = 'Failed. '. $e->getMessage();
            return response()->json(['msg'=>'Failed. '. $e->getMessage()]);
        }
        //return response()->json(['msg'=>$msg]);
    }
}
