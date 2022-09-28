<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class zeroMove extends Controller
{
    public function savings_transactions(){

        // $zero_trans = DB::table('zero_trans_tbl')->where([
        //     ['acct_type', 'monthly savings']
        //     ['posting_date']
        //     ])->get(); 
        $zero_trans = DB::select(DB::raw("SELECT * FROM `zero_trans_tbl` where acct_type = 'monthly savings' and (posting_date between '2020-07-07 00:00:00' and '2021-07-07 00:00:00') order by posting_date asc"));

        $date_time = date('Y-m-d h:i:s');

        foreach ($zero_trans as $zero_tran) {

            //convert date from name to number
            $month = date('m',strtotime($zero_tran->month));

            if($zero_tran->payment_channel == ''){
                $payment_method = 'Paid';
            } else{
                $payment_method = $zero_tran->payment_channel;
            }

            //get the last inserted_id which is the primary id of savings tbl
            $savings_id = DB::table('savings')->insertGetId(['user_id' => $zero_tran->user_id, 'amount' => abs($zero_tran->amount), 'transaction_month' => $month, 'auto_status'=> 0, 'card_id'=> 0, 'start_date' => $zero_tran->date, 'payment_method' => $payment_method, 'created_at' => $date_time]);

            if(((int)$zero_tran->amount > 0) || ($zero_tran->type == 'credit')){
                $savings_category = 1;
                $credit = abs($zero_tran->amount);
                $debit = 0.00;
                $description = 'SAVINGS DEPOSIT';
            } else{
                $savings_category = 52;
                $debit = abs($zero_tran->amount);
                $credit = 0.00;
                $description = 'SAVINGS WITHDRAWAL';
            }

            $entered_by = $this->get_entered_by($zero_tran->user_id);
                
            //insert into the savings_transactions table
            $savings_trans_insert = DB::table('savings_transactions')->insertGetId(['savings_id'=> $savings_id, 'user_id' => $zero_tran->user_id,'paystack_id' => 0, 'savings_type'=> 'self', 'savings_category' => $savings_category, 'status' => 'completed', 'amount' => abs($zero_tran->amount), 'date_time' => $zero_tran->date, 'transaction_by' => $entered_by, 'transaction_type' => $zero_tran->type, 'payment_method' => $payment_method, 'created_at' => $date_time ]);
                        
            //particular user on the savings_balances table
            $savings_bal_tbl = DB::table('savings_balances')->where('user_id', $zero_tran->user_id);

            //check if the user exists
            if($savings_bal_tbl->doesntExist()){

                DB::table('savings_balances')->insert(['user_id' => $zero_tran->user_id, 'savings_balance'=> $zero_tran->amount, 'created_at' => $date_time]);
            } else{
                //pull out single row
                $single = DB::table('savings_balances')->where('user_id', $zero_tran->user_id)->first();

                $total_balance = $single->savings_balance + $zero_tran->amount;
                DB::table('savings_balances')->where('user_id', $zero_tran->user_id)->update(['savings_balance' => $total_balance, 'updated_at' => $date_time]);
            }

            //insert into transactions table
            $transaction_insert = DB::table('transactions')->insert(['user_id' => $zero_tran->user_id, 'amount' => abs($zero_tran->amount), 'paystack_id' => 0, 'payment_method' => $payment_method, 'entry_date' => $zero_tran->date, 'transaction_id'=> $savings_trans_insert, 'transaction_category'=> $savings_category, 'transaction_type' => $zero_tran->type, 'created_at' => $date_time]);

            $last_id = DB::table('savings_acc_statement')->where('user_id', $zero_tran->user_id)->max('id');
            $last_balance = DB::table('savings_acc_statement')->where('id', $last_id)->value('balance');
            $acc_balance = $last_balance + $zero_tran->amount;

            //insert into savings account statement table
            $acct_statement = DB::table('savings_acc_statement')->insert(['user_id'=> $zero_tran->user_id, 'description'=> $description, 'channel'=> $payment_method,'value_date' => $zero_tran->date, 'debit'=> $debit, 'credit'=> $credit, 'balance' => $acc_balance]);
        }
        
        if($acct_statement){
            return response()->json(['message' => 'insertion made successfully','status' => true]);
        } else{
            return response()->json(['message' => 'Failed to insert','status' => false]);
        }
    }



    public function target_transactions(){
        
        // $zero_trans = DB::table('zero_trans_tbl')->where('acct_type', 'target savings')->limit(5)->get();

        $zero_trans = DB::select(DB::raw("SELECT * FROM `zero_trans_tbl` where acct_type = 'target savings' and (posting_date between '2013-12-31 00:00:00' and '2022-09-30 00:00:00') order by posting_date asc"));

        $date_time = date('Y-m-d h:i:s');
        $just_date = date('Y-m-d');

        foreach ($zero_trans as $zero_tran) {
            $abs_amount = abs($zero_tran->amount);
            $paystack_id = mt_rand(10000, 99999);

            if($zero_tran->payment_channel == ''){
                $payment_method = 'Bank';
            } else{
                $payment_method = $zero_tran->payment_channel;
            }

           $target_id =  DB::table('targets')->insertGetId(['user_id' => $zero_tran->user_id, 'payment_method'=>$payment_method, 'created_at' => $date_time, 'updated_at' => $date_time]);
           //Variables for deposit
           if($zero_tran->amount > 0 || $zero_tran->type == 'credit'){
            $trans_type = 'credit';
            $targets_type = 'self';
            $target_category = 2;
            $status = 'completed';
            $withdrawal = 0;
            $tbal1 = $abs_amount;
            //Variables for withdrawal
           } else{
            $trans_type = 'debit';
            $targets_type = 'manual';
            $target_category = 54;
            $status = 'pending';
            $withdrawal = $abs_amount;
            $tbal1 = $zero_tran->amount;
           }

           $entered_by = $this->get_entered_by($zero_tran->user_id);

            //Insert into the target_transactions table and get the inserted id 
            $target_trans_id = DB::table('target_transactions')->insertGetId(['targets_id'=> $target_id, 'user_id' => $zero_tran->user_id, 'date_time' => $zero_tran->date, 'targets_type' => $targets_type, 'amount' => $abs_amount, 'created_at' => $date_time, 'updated_at' => $date_time, 'transaction_by' => $entered_by, 'transaction_type' => $trans_type, 'target_category' => $target_category, 'status' => $status, 'paystack_id' => $paystack_id, 'payment_method' => $payment_method]);

            //Insert into the transactions table
            DB::table('transactions')->insert(['user_id' => $zero_tran->user_id, 'amount'=> $abs_amount, 'entry_date' => $zero_tran->date, 'transaction_id' => $target_trans_id, 'status' => $status, 'transaction_category' => $target_category, 'payment_method'=> $payment_method, 'transaction_type' => $trans_type, 'created_at' => $date_time]);

        }

        return response()->json(['status' => true, 'message' => 'insert successful']);

    }

    public function wallet_transactions(){
        // $zero_trans = DB::table('zero_trans_tbl')->where('acct_type', 'Wallet')->limit(5)->get();

        $zero_trans = DB::select(DB::raw("SELECT * FROM `zero_trans_tbl` where acct_type = 'Wallet' and (posting_date between '2013-12-31 00:00:00' and '2022-09-30 00:00:00') order by posting_date asc"));

        $date_time = date('Y-m-d h:i:s');
        $just_date = date('Y-m-d');

        foreach ($zero_trans as $zero_tran) {
            $abs_amount = abs($zero_tran->amount);
            $entered_by = $this->get_entered_by($zero_tran->user_id);

            //Variables for deposit
           if($zero_tran->amount > 0 || $zero_tran->type == 'credit'){
            $trans_type = 'credit';
            $wallet_category = 7;
            //Variables for withdrawal
           } else{
            $trans_type = 'debit';
            $wallet_category = 53;
           }

           if($zero_tran->payment_channel == ''){
                $payment_method = 'Bank';
            } else{
                $payment_method = $zero_tran->payment_channel;
            }

            $wallet_inserted_id = DB::table('wallets')->insertGetId(['user_id' => $zero_tran->user_id, 'amount'=> $abs_amount, 'entry_date' => $zero_tran->date, 'enter_by' => $entered_by, 'wallet_type' => $trans_type, 'wallet_category' => $wallet_category, 'payment_method' => $payment_method, 'created_at' => $date_time, 'updated_at' => $date_time]);

            //Insert into the transactions table
            $transaction_insert = DB::table('transactions')->insert(['user_id' => $zero_tran->user_id, 'amount'=> $abs_amount, 'payment_method' => $payment_method, 'entry_date' => $zero_tran->date, 'transaction_id' => $wallet_inserted_id, 'transaction_category' => $wallet_category, 'transaction_type' => $trans_type]);
        }
        return response()->json(['status' => true, 'message' => 'insert successful']);      

    }
    
        public function share_holding_transactions(){
            
            // $zero_trans = DB::table('zero_trans_tbl')->where('acct_type', 'shares Capital')->limit(5)->get();

            $zero_trans = DB::select(DB::raw("SELECT * FROM `zero_trans_tbl` where acct_type = 'shares Capital' and (posting_date between '2013-12-31 00:00:00' and '2022-09-30 00:00:00') order by posting_date asc"));

            $date_time = date('Y-m-d h:i:s');
            $just_date = date('Y-m-d');

    
            foreach ($zero_trans as $zero_tran) {
                $abs_amount = abs($zero_tran->amount);
                $paystack_id = mt_rand(10000, 99999);

                if($zero_tran->payment_channel == ''){
                    $payment_method = 'Bank';
                } else{
                    $payment_method = $zero_tran->payment_channel;
                }

                $entered_by = $this->get_entered_by($zero_tran->user_id);

                if(DB::table('share_holdings')->where([['user_id', '=', $zero_tran->user_id],['status', '=', 1]])->doesntExist()) {
                    // ...insert account for user
                    $user_id = $zero_tran->user_id;
                    $status = 1;
                    $share_holding_insert = DB::table('share_holdings')->insert(['user_id' => $user_id, 'status' => $status, 'created_at' => $date_time, 'payment_method'=> $payment_method,'updated_at' => $date_time]);
                }

                //get share_holding id
                $share_id = DB::table('share_holdings')->where([['user_id', '=', $zero_tran->user_id],
                ['status', '=', 1]])->value('id');

                if($zero_tran->amount > 0 || $zero_tran->type == 'credit'){
                    $trans_type = 'credit';

                } else {
                    $trans_type = 'debit';
                }

                //insert into share holdings table
                $share_transaction =  DB::table('shareholdings_transactions')->insertGetId(['share_holdings_id' => $share_id, 'user_id' => $zero_tran->user_id, 'shareholdings_type' => 'manual', 'amount' => $abs_amount, 'investment_amount' => 0, 'status' => 'completed', 'date_time' => $zero_tran->date, 'transaction_by' => $entered_by, 'paystack_id' => $paystack_id, 'transaction_type' => $trans_type, 'payment_method' => $payment_method, 'created_at' => $date_time]);

                //Insert into the transactions table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $zero_tran->user_id, 'amount' => $abs_amount, 'entry_date' => $zero_tran->date, 'payment_method'=>$payment_method,'transaction_id' => $share_transaction, 'transaction_type' => $trans_type, 'transaction_category' => 3, 'created_at' => $date_time, 'updated_at' => $date_time]);
                
            }

            return response()->json(['status' => true, 'message' => 'insertion successful']);

        }

        public function procurement_repayment(){
            // $zero_trans = DB::table('zero_trans_tbl')->where('acct_type', 'Procurement')->limit(5)->get();

            $zero_trans = DB::select(DB::raw("SELECT * FROM `zero_trans_tbl` where acct_type = 'Procurement' and (posting_date between '2013-12-31 00:00:00' and '2022-09-30 00:00:00') order by posting_date asc"));
            $date_time = date('Y-m-d h:i:s');
            $just_date = date('Y-m-d');

    
            foreach ($zero_trans as $zero_tran) {
                $paystack_id = mt_rand(10000, 99999);
                $abs_amount = abs($zero_tran->amount);
                
                if($zero_tran->payment_channel == ''){
                    $payment_method = 'Bank';
                } else{
                    $payment_method = $zero_tran->payment_channel;
                }

                $date = explode('-' , $zero_tran->date);
                $mnt = $date[1];
                $yr = $date[0];
                $month = date('F', mktime(0, 0, 0, $mnt, 10));

                $entered_by = $this->get_entered_by($zero_tran->user_id);
                
                //======hard coded data ==========
                $description = $month .' ' . $yr . ' DEDUCTION';
                $frequency = 'Monthly';
                $start_date = date('Y-m-d'); 
                $app_no = 'ZIMC-'.$zero_tran->user_id;
                $card_id = 0;
                $frequency = 'Monthly';
                $item_size = 'Medium';
                //======hard coded data ==========

                //insert into the procurement table
                DB::table('procurement')->insert(['user_id'=> $zero_tran->user_id, 'app_no' => $app_no, 'status' => 11, 'frequency' => $frequency, 'payment_method'=> $payment_method, 'item_size' => $item_size, 'card_id' => $card_id, 'created_at' => $date_time, 'updated_at' => $date_time]);

                //get the loan_id
                $proc_id = DB::table('procurement')->where([['user_id', '=', $zero_tran->user_id], ['status', '=', 11]])->value('id');

                //insert into the loan repayment table
                $insertedId = DB::table('procurement_repayments')->insertGetId(['procurement_id' => $proc_id, 'user_id' =>$zero_tran->user_id, 'description' => $description, 'repayment_amount' => $abs_amount, 'debit' => '0.00', 'credit' => $abs_amount, 'balance' => 0, 'entry_date' => $zero_tran->date, 'payment_method' => $payment_method, 'paystack_id' => $paystack_id, 'created_at' => $date_time, 'updated_at' => $date_time]);

                //insert into the transaction table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $zero_tran->user_id, 'paystack_id' => $paystack_id, 'amount' => $abs_amount, 'entry_date' => $zero_tran->date, 'payment_method' => $payment_method, 'transaction_id' => $insertedId, 'transaction_type' => 'credit', 'transaction_category' => 23, 'status' => 'completed', 'created_at' => $date_time, 'updated_at' => $date_time]);

            }
            return response()->json(['status' => true, 'message' => 'insertion successful']);
            
        }

        public function loan_repayment(){

            // $zero_trans = DB::table('zero_trans_tbl')->where('acct_type', 'loan')->limit(5)->get();

            $zero_trans = DB::select(DB::raw("SELECT * FROM `zero_trans_tbl` where acct_type = 'loan' and (posting_date between '2013-12-31 00:00:00' and '2022-09-30 00:00:00') order by posting_date asc"));
            $date_time = date('Y-m-d h:i:s');
            $just_date = date('Y-m-d');

            foreach ($zero_trans as $zero_tran) {
                $paystack_id = mt_rand(10000, 99999);
                $abs_amount = abs($zero_tran->amount);

                if($zero_tran->payment_channel == ''){
                    $payment_method = 'Paid';
                } else{
                    $payment_method = $zero_tran->payment_channel;
                }

                //======hard coded data ==========
                $loan_group = 0;
                $loan_amount = 0;
                $frequency = 'Monthly';
                $start_date = date('Y-m-d'); 
                $app_no = 'ZIMC-'.$zero_tran->user_id;
                //======hard coded date ==========
                
                $entered_by = $this->get_entered_by($zero_tran->user_id);

                //insert into the loan table
                DB::table('loan')->insert(['user_id' => $zero_tran->user_id, 'app_no' => $app_no, 'status' => 11, 'payment_method' => $payment_method, 'loan_group' => $loan_group, 'loan_amount' => $loan_amount, 'start_date' => $start_date, 'frequency' => $frequency]);

                //get the loan_id
                $loan_id = DB::table('loan')->where([['user_id', '=', $zero_tran->user_id], ['status', '=', 11]])->value('id');

                //insert into the loan repayment table and get id
                $insertedId = DB::table('loan_repayment')->insertGetId(['loan_id' => $loan_id, 'user_id' => $zero_tran->user_id, 'repayment_amount' => $abs_amount, 'debit' => '0.00', 'credit' => $abs_amount, 'balance' => 0, 'trans_date' => $zero_tran->date, 'payment_method' => $payment_method, 'paystack_id' => $paystack_id, 'created_at' => $date_time, 'updated_at' => $date_time]);

                //insert into the transactions table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $zero_tran->user_id, 'paystack_id' => $paystack_id, 'amount' => $abs_amount, 'entry_date' => $zero_tran->date, 'payment_method' => $payment_method, 'transaction_id' => $insertedId, 'transaction_type' => 'credit', 'transaction_category' => 4, 'status' => 'completed', 'created_at' => $date_time, 'updated_at' => $date_time]);

            }

            return response()->json(['status' => true, 'message' => 'insertion successful']);
        }

        public function membership_registration(){
            // $members = DB::table('members_tbl')->select(select::raw())get();
            $members = DB::select(DB::raw("SELECT * FROM `members_tbl` where membership_no = 0"));

            // $getam = array();
            foreach ($members as $member) {
                $member_id = "ZEROCOOP-" . $member->membership_no;
                if(DB::table('users')->where('member_id', $member_id)->doesntExist()){

                    if($member->other_name == '' || $member->other_name == 'x'){
                        $first_name = $member->surname;
                        $middle_name = 'nil';
                        $last_name = 'nil';
                    }
                    else{
                        $first_name = $member->other_name;
                        $middle_name = 'nil';
                        $last_name = $member->surname;
                    }
                    
                    //check if user has employee number i.e if civil servant
                    if($member->employee_no == '' || $member->employee_no == 'x'){
                        $oracle_no = '';
                        $withdrawal_waiver = 0;
                        $membership_type = 'cash member';
                        $membership_type_status = 0;
                    }else {
                        $oracle_no = $member->employee_no;
                        $withdrawal_waiver = 1;
                        $membership_type = 'civil servant';
                        $membership_type_status = 1;
                    }
                    $online_banking = 1;
                    $password = '$2y$10$fhi2zbz17vdvn6KWJBeK6OiS3cAevJLxY/CsHwDgu1s/sIKxi42D6';
                    $member_id = "ZEROCOOP-" . $member->membership_no;
                    $membership_type_status = 0;

                    $dataz = ['first_name' => $first_name, 'last_name' => $last_name, 'middle_name' => $middle_name, 'email' => $member->email, 'phone_no' => $member->phone_no, 'member_id'=> $member_id, 'membership_type' => $membership_type, 'membership_type_status' => $membership_type_status,'online_banking' => $online_banking, 'password' => $password, 'gender' => $member->gender, 'dob'=> $member->date_of_birth];

                    $get_user_id = DB::table('users')->insertGetId($dataz);

                    // array_push($getam,$get_user_id);

                    if(DB::table('profiles')->where('user_id', $get_user_id)->doesntExist()){
                        $profiles = DB::table('profiles')->insert(["user_id" => $get_user_id, "last_name" => $last_name, 'relationship' => $member->kin_relationship, 'state_of_origin'=> $member->state_of_origin, 'local_govt'=> $member->lga, 'marital_status'=> $member->marital_status, 'occupation' => $member->profession, "first_name" => $first_name, "email" => $member->email, "phone_no" => $member->phone_no, "date_entered" => date('Y-m-d'), "created_at" => date('Y-m-d h:i:s')]);
                    }
                    unset($dataz['middle_name']);
                    unset($dataz['online_banking']);
                    unset($dataz['password']);
                    unset($dataz['withdrawal_waiver']);
                    unset($dataz['gender']);
                    unset($dataz['dob']);

                    $dataz['created_at'] = date('Y-m-d h:i:s');
                    $dataz['entry_date'] = date('Y-m-d');

                    $get_member_id = DB::table('users')->where('id', $get_user_id)->value('member_id');
                    
                    if(DB::table('tblusers_approval')->where('member_id', $get_member_id)->doesntExist()){
                        DB::table('tblusers_approval')->insert($dataz);
                    }
                
                }

            }

            return response()->json(['status' => true,'message' => 'insertion successful']);
        }

        public function membership_registration_fee(){

            $zero_trans = DB::table('zero_trans_tbl')->where([
                ['description', 'like', '%'.'membership application'.'%'],
                ['user_id', '<>', '']
                ])->get();

            $date_time = date('Y-m-d h:i:s');
            $just_date = date('Y-m-d');

            // hard coded
            $paystack_id = mt_rand(10000, 999999);
            // hard coded

            foreach ($zero_trans as $zero_tran) {

                if(DB::table('member_registration')->where('user_id', $zero_tran->user_id)->doesntExist()){

                    if($zero_tran->payment_channel == ''){
                        $payment_method = 'Paid';
                    } else{
                        $payment_method = $zero_tran->payment_channel;
                    }

                    if(DB::table('users')->where('id', $zero_tran->user_id)->doesntExist()){
                        continue;
                    }
                    $single_user =  DB::table('users')->where('id', $zero_tran->user_id)->first();

                    $data = ['user_id' => $single_user->id, 'last_name' => $single_user->last_name, 'first_name' => $single_user->first_name, 'middle_name' => $single_user->middle_name, 'phone_no' => $single_user->phone_no, 'email' => $single_user->email, 'gender' => $single_user->gender, 'date_of_birth' => $single_user->dob,'entry_date' => date('Y-m-d'), 'created_at' => date('Y-m-d h:i:s')];
                    DB::table('member_registration')->insert($data);

                    $member_registration_id = DB::table('member_registrations')->insertGetId(['user_id' => $single_user->id, 'paystack_id' => $paystack_id, 'amount' => abs($zero_tran->amount), 'trans_date' => $zero_tran->date, 'created_at' => date('Y-m-d h:i:s')]);

                    $transaction_insert = DB::table('transactions')->insert(['user_id' => $single_user->id, 'paystack_id' => $paystack_id, 'amount' => abs($zero_tran->amount), 'entry_date' => $zero_tran->date, 'payment_method' => $payment_method, 'transaction_id' => $member_registration_id, 'transaction_type' => 'credit', 'transaction_category' => 20, 'status' => 'completed', 'created_at' => $date_time, 'updated_at' => $date_time]);

                    $m_status = 1;

                    DB::table('users')->where('id', $single_user->id)->update(['member_status' => $m_status, 'user_status' => $m_status]);
                }
                
            }
            return response()->json(['status' => true, 'message' => 'data inserted successfully']);
        }

        public function enable_disable(){
            $members = DB::select(DB::raw("SELECT * FROM `members_tbl` where 1"));

            foreach ($members as $member) {

                $check = $member->status;
                if($check == 'Active'){
                    $active = DB::table('members_tbl')->where('status', 'Active')->value('membership_no');
                    $member_id = 'ZEROCOOP-' . $active;
                    DB::table('users')->where('member_id', $member_id)->update(['user_status' => 1, 'member_status' => 1]);
                } else {
                    $closed = DB::table('members_tbl')->where('status', 'Closed')->value('membership_no');
                    $member_id = 'ZEROCOOP-' . $closed;
                    DB::table('users')->where('member_id', $member_id)->update(['user_status' => 2, 'member_status' => 1]);
                }

            }
            return response()->json(['message' => 'updated successfully']);
        }





        private function get_entered_by($user_ids){
            $poster = DB::table('zero_trans_tbl')->where('user_id', $user_ids)->value('posted_by');
            
            if($poster == ''){
                $poster = 'Administrator zerointerest';
            } else{
                $poster;
            }
            $x = explode(' ', $poster);
            $y = $x[0];
            $z = $x[1];

            //using the surname and other_name, get poster_id 
            $poster_id1 = DB::table('staff_role')->where([
            ['surname', '=', $y],
            ['other_name', '=', $z]])->value('id');
            if($poster_id1 == null){
               return $poster_id = 0;
            } else{
               return $poster_id = $poster_id1;
            }
        }
































//======================= Target Balance =======================
//==============================================================
            //Insert || Update the target_balances table
            // $tb_query = DB::table('target_balances')->where([
            //     ['user_id', '=', $zero_tran->user_id],
            //     ['targets_id', '=', $target_id]])->first();
            // // $single_row = $tb_query;
            // return $tb_query;

            // if($zero_tran->amount > 0 || $zero_tran->type == 'credit'){
            //     $tb_query = DB::table('target_balances')->where([
            //         ['user_id', '=', $zero_tran->user_id],
            //         ['targets_id', '=', $target_id]]);
 
            //         if($tb_query->exists()){
            //             $single_row = $tb_query->first();
            //             $target_bal = $single_row->target_balance + $abs_amount; 
            //             $new_bal = $target_bal - $single_row->withdrawal;

            //             $tb_query->update(['target_balance' => $target_bal, 'balance' => $new_bal, 'updated_at' => $date_time]);

            //         }
            //         DB::table('target_balances')->insert(['user_id' => $zero_tran->user_id, 'targets_id' => $target_id, 'target_balance' => $abs_amount, 'balance' => $abs_amount, 'status' => 1, 'created_at' => $date_time, 'updated_at' => $date_time]);

            // } else{

            //     $tb_query = DB::table('target_balances')->where('targets_id', '=', $target_id);
            //     $single_row = $tb_query->first(); 

            //     if($tb_query->exists()){
            //         $withdrawal_bal = $abs_amount + $single_row->withdrawal; //total withdrawal so far
            //         $new_balance = $single_row->target_balance - $withdrawal_bal; //balance
            //         $tb_query->update(['withdrawal' => $withdrawal_bal, 'balance' => $new_balance]);
            //     }
            // }
            //======================= Target Balance =======================
            //==============================================================
}
