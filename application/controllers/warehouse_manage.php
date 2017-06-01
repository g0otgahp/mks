<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class warehouse_manage extends CI_Controller {

	// public function warehouse_in()
	// {
	// 	@session_start();
	// 	$warehouse = array(
	// 		'warehouse_product' => $this->input->post('warehouse_product'),
	// 		'warehouse_type' => "in",
	// 		'warehouse_amount' => $this->input->post('warehouse_amount'),
	// 		'warehouse_date' => date('Y-m-d'),
	// 		'warehouse_time' => date('H:i:s'),
	// 		'warehouse_employees' => @$_SESSION['employees_id']
	// 	);
	// 	$this->warehouse_model->warehouse_in($warehouse);
	// 	redirect('warehouse/warehouse_list');
	// }
	// public function warehouse_out()
	// {
	// 	@session_start();
	// 	$warehouse = array(
	// 		'warehouse_product' => $this->input->post('warehouse_product'),
	// 		'warehouse_type' => "out",
	// 		'warehouse_amount' => $this->input->post('warehouse_amount'),
	// 		'warehouse_date' => date('Y-m-d'),
	// 		'warehouse_time' => date('H:i:s'),
	// 		'warehouse_employees' => @$_SESSION['employees_id']
	// 	);
	// 	$this->warehouse_model->warehouse_out($warehouse);
	//
	// 	$stock = array(
	// 		'stock_product' => $this->input->post('warehouse_product'),
	// 		'stock_type' => "in",
	// 		'stock_amount' => $this->input->post('warehouse_amount'),
	// 		'stock_date' => date('Y-m-d'),
	// 		'stock_time' => date('H:i:s'),
	// 		'stock_employees' => @$_SESSION['employees_id'],
	// 		'stock_shop' => $this->input->post('warehouse_shop')
	// 	);
	// 	$this->stock_model->stock_in($stock);
	// 	redirect('warehouse/warehouse_list');
	// }

	public function warehouse_in_temp()
	{
		@session_start();
		date_default_timezone_set("Asia/Bangkok");
		$warehouse = array(
			'warehouse_temp_product' => $this->input->post('warehouse_product'),
			'warehouse_temp_type' => "in",
			'warehouse_temp_amount' => $this->input->post('warehouse_amount'),
			'warehouse_temp_date' => date('Y-m-d'),
			'warehouse_temp_time' => date('H:i:s'),
		);
		$this->warehouse_model->warehouse_temp_insert($warehouse);
		redirect('warehouse/warehouse_in');
	}
	public function warehouse_out_temp()
	{
		@session_start();
		date_default_timezone_set("Asia/Bangkok");
		$balance = 1;
		$detail = array();
		$product[0]['product_code'] = $this->input->post('warehouse_product');
		$warehouse_amount = $this->input->post('warehouse_amount');
		$warehouse = array(
			'warehouse_temp_product' => $this->input->post('warehouse_product'),
			'warehouse_temp_type' => "out",
			'warehouse_temp_amount' => $warehouse_amount,
			'warehouse_temp_date' => date('Y-m-d'),
			'warehouse_temp_time' => date('H:i:s'),
			'warehouse_temp_shop' => $this->input->post('warehouse_shop'),
		);

		$product_detail = $this->product_model->product_find($product[0]['product_code']);
		$product = $this->product_model->warehouse_balance($product);
		$warehouse_out_temp = $this->warehouse_model->warehouse_out_temp_list();
		// print_r($warehouse_out_temp);
		// exit();
		$w_temp = 0;
		if (count($warehouse_out_temp) > 0) {
			foreach ($warehouse_out_temp as $w) {
				if ($w['product_code'] == $product[0]['product_code']) {
					$w_temp += $w['warehouse_temp_amount'];
				}
			}
		}
		$w_temp_sum = $w_temp + $warehouse_amount;
		// print_r($warehouse_amount);
		// exit();
		if ($product[0]['warehouse_balance'] >= $w_temp_sum) {
			$this->warehouse_model->warehouse_temp_insert($warehouse);
			redirect('warehouse/warehouse_out');
		} else {
			$balance = 0;
			$detail = $warehouse;
			$data['balance'] = array(
				'balance' => $balance,
				'w_temp' => $w_temp,
				'w_temp_sum' => $w_temp_sum,
				'warehouse_amount' => $warehouse_amount,
				'product_name' => $product_detail['product_name'],
				'product_code' => $product[0]['product_code'],
				'warehouse_balance' => $product[0]['warehouse_balance']
			 );

			 @session_start();

 			$data['config'] = $this->config_model->config();

 			if(@$_SESSION['employees_id']!=""){
 				$data['shop'] = $this->shop_model->shop_list();
 				$data['allproduct'] = $this->product_model->product_list();
 				$data['product'] = $this->warehouse_model->warehouse_out_temp_list();
 				$data['page'] = "warehouse/warehouse_out";
 				$this->load->view('head',$data);
 			}else{
 				redirect('login/index');
 			}
		}


	}
}
