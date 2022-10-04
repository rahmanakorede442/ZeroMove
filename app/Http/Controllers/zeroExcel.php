<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class zeroExcel extends zeroMove
{
    
    public function zero_uni_trans(Request $request){
        $data = json_decode($request->getContent(), true);
        $firsts = $data['data'];
        
        foreach ($firsts as $first) {

            $package_type = $first['transaction_type'];
            $abs_amount = abs($first['amount']);
            $user_id = $first['user_id'];
            $date = date('Y-m-d');
            $date_time = date('Y-m-d h:i:s');

            if($package_type == 'wallet'){

                // $entered_by = $this->get_entered_by($first['user_id']);
                $trans_type = 'credit';
                $payment_method = 'Bank';
                $wallet_category = 7;
                $entered_by = 12;

                $wallet_inserted_id = DB::table('wallets')->insertGetId(['user_id' => $user_id, 'amount'=> $abs_amount, 'entry_date' => $date, 'enter_by' => $entered_by, 'wallet_type' => $trans_type, 'wallet_category' => $wallet_category, 'payment_method' => $payment_method, 'created_at' => $date_time, 'updated_at' => $date_time]);
    
                //Insert into the transactions table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $user_id, 'amount'=> $abs_amount, 'payment_method' => $payment_method, 'entry_date' => $date, 'transaction_id' => $wallet_inserted_id, 'transaction_category' => $wallet_category, 'transaction_type' => $trans_type, 'created_at' => $date_time, 'updated_at' => $date_time]); 

                return response()->json(['message'=> 'wallet insertion made']);

            } else if($package_type == 'loan'){
  
                //======hard coded data ==========
                $payment_method = 'Paid';
                $paystack_id = mt_rand(10000, 99999);
                $loan_group = 0;
                $loan_amount = 0;
                $frequency = 'Monthly';
                $start_date = date('Y-m-d'); 
                $app_no = 'ZIMC-'.$user_id;
                $entered_by = 23;
                //======hard coded date ==========
                
                //insert into the loan table
                DB::table('loan')->insert(['user_id' => $user_id, 'app_no' => $app_no, 'status' => 11, 'payment_method' => $payment_method, 'loan_group' => $loan_group, 'loan_amount' => $loan_amount, 'start_date' => $start_date, 'frequency' => $frequency]);

                //get the loan_id
                $loan_id = DB::table('loan')->where([['user_id', '=', $user_id], ['status', '=', 11]])->value('id');

                //insert into the loan repayment table and get id
                $insertedId = DB::table('loan_repayment')->insertGetId(['loan_id' => $loan_id, 'user_id' => $user_id, 'repayment_amount' => $abs_amount, 'debit' => '0.00', 'credit' => $abs_amount, 'balance' => 0, 'trans_date' => $date, 'payment_method' => $payment_method, 'paystack_id' => $paystack_id, 'created_at' => $date_time, 'updated_at' => $date_time]);

                // insert into the transactions table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $user_id, 'paystack_id' => $paystack_id, 'amount' => $abs_amount, 'entry_date' => $date, 'payment_method' => $payment_method, 'transaction_id' => $insertedId, 'transaction_type' => 'credit', 'transaction_category' => 4, 'status' => 'completed', 'created_at' => $date_time, 'updated_at' => $date_time]);
                return response()->json(['message'=> 'loan insertion made']);

            } else if($package_type == 'procurement'){

                $payment_method = 'Bank';
                // $date1 = date('Y-m-d');
                $date1 = explode('-' , $date);
                $mnt = $date1[1];
                $yr = $date1[0];
                $month = date('F', mktime(0, 0, 0, $mnt, 10));

                // $entered_by = $this->get_entered_by($zero_tran->user_id);
                
                //======hard coded data ==========
                $description = $month .' ' . $yr . ' DEDUCTION';
                $frequency = 'Monthly';
                $start_date = date('Y-m-d'); 
                $app_no = 'ZIMC-'.$user_id;
                $card_id = 0;
                $item_size = 'Medium';

                $paystack_id = 0;
                //======hard coded data ==========

                //insert into the procurement table
                DB::table('procurement')->insert(['user_id'=> $user_id, 'app_no' => $app_no, 'status' => 11, 'frequency' => $frequency, 'payment_method'=> $payment_method, 'item_size' => $item_size, 'card_id' => $card_id, 'created_at' => $date_time, 'updated_at' => $date_time]);

                //get the loan_id
                $proc_id = DB::table('procurement')->where([['user_id', '=', $user_id], ['status', '=', 11]])->value('id');

                //insert into the loan repayment table
                $insertedId = DB::table('procurement_repayments')->insertGetId(['procurement_id' => $proc_id, 'user_id' =>$user_id, 'description' => $description, 'repayment_amount' => $abs_amount, 'debit' => '0.00', 'credit' => $abs_amount, 'balance' => 0, 'entry_date' => $date, 'payment_method' => $payment_method, 'paystack_id' => $paystack_id, 'created_at' => $date_time, 'updated_at' => $date_time]);

                //insert into the transaction table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $user_id, 'paystack_id' => $paystack_id, 'amount' => $abs_amount, 'entry_date' => $date, 'payment_method' => $payment_method, 'transaction_id' => $insertedId, 'transaction_type' => 'credit', 'transaction_category' => 23, 'status' => 'completed', 'created_at' => $date_time, 'updated_at' => $date_time]);
                return response()->json(['message'=> 'procurement insertion made']);

            } else if($package_type == 'target savings'){
                $paystack_id = 0;
                $trans_type = 'credit';
                $targets_type = 'self';
                $target_category = 2;
                $status = 1;
                $withdrawal = 0;
                $tbal1 = $abs_amount;
                $payment_method = 'Bank';
                $entered_by = 23;

                $target_id =  DB::table('targets')->insertGetId(['user_id' => $user_id, 'amount'=>$abs_amount, 'payment_method'=>$payment_method, 'status'=>$status, 'created_at' => $date_time, 'updated_at' => $date_time]);
        


    
            //    $entered_by = $this->get_entered_by($zero_tran->user_id);
    
                //Insert into the target_transactions table and get the inserted id 
                $target_trans_id = DB::table('target_transactions')->insertGetId(['targets_id'=> $target_id, 'user_id' => $user_id, 'date_time' => $date, 'targets_type' => $targets_type, 'amount' => $abs_amount, 'created_at' => $date_time, 'updated_at' => $date_time, 'transaction_by' => $entered_by, 'transaction_type' => $trans_type, 'target_category' => $target_category, 'status' => $status, 'paystack_id' => $paystack_id, 'payment_method' => $payment_method]);
    
                //Insert into the transactions table
                DB::table('transactions')->insert(['user_id' => $user_id, 'amount'=> $abs_amount, 'entry_date' => $date, 'transaction_id' => $target_trans_id, 'status' => $status, 'transaction_category' => $target_category, 'payment_method'=> $payment_method, 'transaction_type' => $trans_type, 'created_at' => $date_time]);
                return response()->json(['message'=> 'target insertion made']);

            } else if($package_type == 'share capital'){
  
                $payment_method = 'Bank';
                $entered_by = 23;
                $trans_type = 'credit';
                $paystack_id = 0;
                // $entered_by = $this->get_entered_by($zero_tran->user_id);

                if(DB::table('share_holdings')->where([['user_id', '=', $user_id],['status', '=', 1]])->doesntExist()) {
                    // ...insert account for user
                    $user_id1 = $user_id;
                    $status = 1;
                    $share_holding_insert = DB::table('share_holdings')->insert(['user_id' => $user_id1, 'status' => $status, 'created_at' => $date_time, 'payment_method'=> $payment_method,'updated_at' => $date_time]);
                }

                //get share_holding id
                $share_id = DB::table('share_holdings')->where([['user_id', '=', $user_id],
                ['status', '=', 1]])->value('id');

                

                //insert into share holdings table
                $share_transaction =  DB::table('shareholdings_transactions')->insertGetId(['share_holdings_id' => $share_id, 'user_id' => $user_id, 'shareholdings_type' => 'manual', 'amount' => $abs_amount, 'investment_amount' => 0, 'status' => 'completed', 'date_time' => $date, 'transaction_by' => $entered_by, 'paystack_id' => $paystack_id, 'transaction_type' => $trans_type, 'payment_method' => $payment_method, 'created_at' => $date_time]);

                //Insert into the transactions table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $user_id, 'amount' => $abs_amount, 'entry_date' => $date, 'payment_method'=>$payment_method,'transaction_id' => $share_transaction, 'transaction_type' => $trans_type, 'transaction_category' => 3, 'created_at' => $date_time, 'updated_at' => $date_time]);

                return response()->json(['message'=> 'share capital insertion made']);

            } else if($package_type == 'regular savings'){

                $month = date('m',strtotime('October'));

                $payment_method = 'Paid';
                // get the last inserted_id which is the primary id of savings tbl
                $savings_id = DB::table('savings')->insertGetId(['user_id' => $user_id, 'amount' => $abs_amount, 'transaction_month' => $month, 'auto_status'=> 0, 'card_id'=> 0, 'start_date' => $date, 'payment_method' => $payment_method, 'created_at' => $date_time]);
    
                
                    $savings_category = 1;
                    $credit = $abs_amount;
                    $debit = 0.00;
                    $description = 'SAVINGS DEPOSIT';
    
                $entered_by = 23;
                    
                // insert into the savings_transactions table
                $savings_trans_insert = DB::table('savings_transactions')->insertGetId(['savings_id'=> $savings_id, 'user_id' => $user_id,'paystack_id' => 0, 'savings_type'=> 'self', 'savings_category' => $savings_category, 'status' => 'completed', 'amount' => $abs_amount, 'date_time' => $date, 'transaction_by' => $entered_by, 'transaction_type' => 'credit', 'payment_method' => $payment_method, 'created_at' => $date_time ]);
                            
                // particular user on the savings_balances table
                $savings_bal_tbl = DB::table('savings_balances')->where('user_id', $user_id);
    
                // check if the user exists
                if($savings_bal_tbl->doesntExist()){
    
                    DB::table('savings_balances')->insert(['user_id' => $user_id, 'savings_balance'=> $abs_amount, 'created_at' => $date_time]);
                } else{
                    //pull out single row
                    $single = DB::table('savings_balances')->where('user_id', $user_id)->first();
    
                    $total_balance = $single->savings_balance + $abs_amount;
                    DB::table('savings_balances')->where('user_id', $user_id)->update(['savings_balance' => $total_balance, 'updated_at' => $date_time]);
                }
    
                //insert into transactions table
                $transaction_insert = DB::table('transactions')->insert(['user_id' => $user_id, 'amount' => $abs_amount, 'paystack_id' => 0, 'payment_method' => $payment_method, 'entry_date' => $date, 'transaction_id'=> $savings_trans_insert, 'transaction_category'=> $savings_category, 'transaction_type' => 'credit', 'created_at' => $date_time]);
    
                $last_id = DB::table('savings_acc_statement')->where('user_id', $user_id)->max('id');
                $last_balance = DB::table('savings_acc_statement')->where('id', $last_id)->value('balance');
                $acc_balance = $last_balance + $abs_amount;
    
                // insert into savings account statement table
                $acct_statement = DB::table('savings_acc_statement')->insert(['user_id'=> $user_id, 'description'=> $description, 'channel'=> $payment_method,'value_date' => $date, 'debit'=> $debit, 'credit'=> $credit, 'balance' => $acc_balance]);

                return response()->json(['message'=> 'savings insertion made']);
            }
        }
    }














    public function trans_file_upload(){
        // if ($request->has('letter_of_appointment')) {
        //     $image2 = $request->file('letter_of_appointment'); //get selected file/image

        //     if($image2->getClientOriginalExtension() != 'jpeg' && $image2->getClientOriginalExtension() != 'png' && $image2->getClientOriginalExtension() != 'jpg' && $image2->getClientOriginalExtension() != 'pdf'){
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'File required must be in pdf, jpg, jpeg or png format!'
        //         ], 200);
        //     }

        //     //no files larger than 700kb
        //     if ($image2->getSize() > 700000){
        //         //respond not validated, file too big.
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'file size must not be greater than 700kb!'
        //         ], 200);
        //     }
            
        //     $filename2 = URL::to("/") . '/credentials/' . time() . 'C.' . $image2->getClientOriginalExtension();
        //     $destinationPath = public_path() . '/credentials'; // upload path
        //     $image2->move($destinationPath, $filename2); // move to folder path
        //     $new_image2 = $filename2;
    }


    public function import_excel(Request $request){
        //return $this->guard()->user();
        $file = $request->file('uploaded_file');

        if ($file) {
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension(); //Get extension of uploaded file
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize(); //Get size of uploaded file in bytes

            //Check for file extension and size
            $valid_extension = array("csv", "xlsx", "xls"); //Only want csv and excel files
            $maxFileSize = 2097152; // Uploaded file size limit is 2mb
            if (in_array(strtolower($extension), $valid_extension)) {

                if ($fileSize <= $maxFileSize) {
                } 
                else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Uploaded file size limit is 2mb'
                    ], 200);
                }
            } 
            else {
                return response()->json([
                    'status' => false,
                    'message' => 'Allowed file csv, xls or xlsx'
                ], 200);
            }

            //Where uploaded file will be stored on the server 
            $location = 'credentials'; //Created an "uploads" folder for that
            // Upload file
            $file->move($location, $filename);
            // In case the uploaded file path is to be stored in the database 
            $filepath = public_path($location . "/" . $filename);
           


            //================================================================
            // Reading file
            //================================================================
            $file = fopen($filepath, "r");
            $importData_arr = array(); // Read through the file and store the contents as an array
            $i = 0;
            //Read the contents of the uploaded file 
            while (($filedata = fgetxls($file, 1000, ",")) !== FALSE) {
                $num = count($filedata);
                // Skip first row (Remove below comment if you want to skip the first row)
                if ($i == 0) {
                    $i++;
                    continue;
                }

                for ($c = 0; $c < $num; $c++) {
                    $importData_arr[$i][] = $filedata[$c];
                }
                $i++;
            }

            fclose($file); //Close after reading

            $j = 0;

           
            foreach ($importData_arr as $importData) {

                $first_name = $importData[1]; 
                // $middle_name = $importData[2]; 
                // $last_name = $importData[3]; 
                // $email = $importData[4]; 
                // $member_type = $importData[5]; 

                $j++;

                //=================================================================
                //get each cell data and insert into users and profile table
                //=================================================================

                // try {
                //     DB::beginTransaction();
                //     Player::create([
                //     'name' => $importData[1],
                //     'club' => $importData[2],
                //     'email' => $importData[3],
                //     'position' => $importData[4],
                //     'age' => $importData[5],
                //     'salary' => $importData[6]
                //     ]);
                //     //Send Email
                //     $this->sendEmail($email, $name);
                //     DB::commit();
                // } catch (\Exception $e) {
                // //throw $th;
                // DB::rollBack();
                // }
            }

            return response()->json([
                'status' => true,
                'message' => $j . " records successfully uploaded"
            ], 200);


        } else {
            //no file was uploaded
            return response()->json([
                'status' => false,
                'message' => 'No file was uploaded'
            ], 200);
        }
    }
}
