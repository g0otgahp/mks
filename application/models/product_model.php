<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class product_model extends CI_Model {

	public function product_list()
	{
		$this->db->order_by('product.product_id','ASC');
		$this->db->join('category','category.category_id = product.product_category');
		$product = $this->db->get('product')->result_array();

		$product = $this->stock_balance($product);
		return $product;
	}
	public function product_find($value)
	{
		$this->db->where('product.product_id', $value);
		$this->db->or_where('product.product_code', $value);
		$this->db->join('category','category.category_id = product.product_category');
		$product = $this->db->get('product')->result_array();
		if (count($product) > 0) {
			$product = $product[0];
		}
		return $product;
	}
	public function product_w_list()
	{
		$this->db->order_by('product.product_id','ASC');
		$this->db->join('category','category.category_id = product.product_category');
		$product = $this->db->get('product')->result_array();

		$product = $this->warehouse_balance($product);
		return $product;
	}

	 public function stock_balance($product)
	 {
		 $i = 0;
		 foreach ($product as $p) {
			 $stock = $this->db
			 ->join('product', 'product.product_code = stock.stock_product')
			 ->where('stock.stock_product', $p['product_code'])
			 ->get('stock')->result_array();
			 $stock_balance = 0;

			 foreach ($stock as $s) {
				 if ($s['stock_type'] == 'in') {
					 $stock_balance += $s['stock_amount'];
				 } else {
					 $stock_balance -= $s['stock_amount'];
				 }
			 }
			 $product[$i]['stock_balance'] = $stock_balance;
			 $i++;

		 }
		 return $product;
	 }
	 public function warehouse_balance($product)
	 {
		//  echo "<pre>";
		//  print_r($product);
		//  exit();
	 	$i = 0;
	 	foreach ($product as $p) {
	 		$warehouse = $this->db
	 		->join('product', 'product.product_code = warehouse.warehouse_product')
	 		->where('warehouse.warehouse_product', $p['product_code'])
	 		->get('warehouse')->result_array();
			//  print_r($warehouse);
			//  exit();
	 		$warehouse_balance = 0;
	 		foreach ($warehouse as $s) {
	 			if ($s['warehouse_type'] == 'in') {
	 				$warehouse_balance += $s['warehouse_amount'];
	 			} else {
	 				$warehouse_balance -= $s['warehouse_amount'];
	 			}
	 		}
	 		$product[$i]['warehouse_balance'] = $warehouse_balance;
			$i++;
	 	}
	 	return $product;
	 }
	public function product_list_by_code($product_code)
	{
		$this->db->order_by('product.product_id','desc');
		$this->db->where('product_code',$product_code);
		$this->db->join('category','category.category_id = product.product_category');
		$query = $this->db->get('product');
		return $query->result_array();
	}

	public function product_list_by_shop($shop_id)
	{
		$this->db->order_by('product.product_id','desc');
		$this->db->join('product','product.product_code = product_limit.product_limit_product_code');
		$this->db->where('product_limit_shop_id',$shop_id);
		$this->db->join('category','category.category_id = product.product_category');
		$query = $this->db->get('product_limit');
		return $query->result_array();
	}

	public function report_product_list($input)
	{
		// echo "<pre>";
		$product['sale_order_item'] = $this->product_list();

		$i=0;
		foreach ($product['sale_order_item'] as $row) {
			// $this->db->select_sum('stock_amount');
			// $this->db->select_sum('stock_price');
			$this->db->where('stock_product', $row['product_code']);
			$this->db->where('stock.stock_type',"out");
			$this->db->where('stock.stock_date >=',$input['date_start']);
			$this->db->where('stock.stock_date <=',$input['date_end']);
			$query = $this->db->get('stock')->result_array();

			$product['sale_order_item'][$i]['sum_stock_amount'] = 0;
			$product['sale_order_item'][$i]['sum_stock_price'] = 0;
			$product['sale_order_item'][$i]['sum_stock_discount'] = 0;
			foreach ($query as $item) {
				echo $item['stock_price'];
				$product['sale_order_item'][$i]['sum_stock_amount'] += $item['stock_amount'];
				$product['sale_order_item'][$i]['sum_stock_price']  += $item['stock_price']*$item['stock_amount'];
				if ($product['sale_order_item'][$i]['product_sale']> $item['stock_price']) {
					$product['sale_order_item'][$i]['sum_stock_discount'] += ($product['sale_order_item'][$i]['product_sale']-$item['stock_price']);
				}

				if ($product['sale_order_item'][$i]['sum_stock_amount']<1) {
					unset($product['sale_order_item'][$i]);
				}
			}

			$i++;
		}

		$product['sale_summary'] = array();
		$product['sale_summary']['sum_buy'] = 0;
		$product['sale_summary']['sum_cost'] = 0;
		$product['sale_summary']['sum_sale'] = 0;
		$product['sale_summary']['sum_price'] = 0;
		$product['sale_summary']['sum_discount'] = 0;

		foreach ($product['sale_order_item'] as $item) {
			$product['sale_summary']['sum_buy'] += $item['product_buy'];
			$product['sale_summary']['sum_sale'] += $item['product_sale'];
			$product['sale_summary']['sum_cost'] += ($item['product_buy']*$item['sum_stock_amount']);

			$product['sale_summary']['sum_price'] += $item['sum_stock_price'];
			$product['sale_summary']['sum_discount'] += $item['sum_stock_discount'];
		}

		$this->db->select_sum('sale_order_detail_discount');
		$this->db->where('sale_order_detail.sale_order_detail_date >=',$input['date_start']);
		$this->db->where('sale_order_detail.sale_order_detail_date <=',$input['date_end']);
		$this->db->where('sale_order_detail.sale_order_detail_status',1);
		$product['sale_order_detail'] = $this->db->get('sale_order_detail')->result_array();


		// print_r($product);
		// exit();
		return $product;
	}

	public function product_insert($product)
	{
		$this->db->insert('product',$product);
	}
	public function product_details($product_id)
	{
		$this->db->where('product.product_id',$product_id);
		$this->db->join('category','category.category_id = product.product_category');
		$query = $this->db->get('product');
		return $query->result_array();
	}
	public function product_update($product)
	{
		$this->db->where('product_id',$product['product_id']);
		$this->db->update('product',$product);
	}
	public function product_delete($product_id)
	{
		$this->db->where('product_id',$product_id);
		$this->db->delete('product');
	}
}
