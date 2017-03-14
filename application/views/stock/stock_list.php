<div id="page-wrapper">
  <div class="row">
    <div class="col-lg-12">
      <h1>สต๊อกสินค้าของร้าน <small>ระบบบริหารจัดการคลังสินค้า <?php echo $config[0]['config_shop_name'] ?></small></h1>
      <ol class="breadcrumb">
        <li class="active"><i class="fa fa-th-large"></i> Stock List</li>
      </ol>
    </div>
  </div>
  <!-- /.row -->
  <div align="right"><p><?php echo anchor('stock/stock_in','<button type="button" class="btn btn-primary" style="width:95px;">รับเข้า</button>')?></p></div>
  <table class="table table-bordered table-hover table-striped tablesorter">
    <thead>
      <tr>
        <th width="5%"><div align="center">ลำดับ</div></th>
        <th width="10%"><div align="center">รหัสสินค้า <i class="fa fa-sort"></i></div></th>
        <th width="10%"><div align="center">ประเภทสินค้า <i class="fa fa-sort"></i></div></th>
        <th width="20%"><div align="center">ชื่อสินค้า <i class="fa fa-sort"></i></div></th>
        <th width="15%"><div align="center">จำนวนคงเหลือ <i class="fa fa-sort"></i></div></th>
        <th width="7%"><div align="center">หน่วย</div></th>
        <th width="15%"><div align="center">สถานะ</div></th>
        <th width="20%"><div align="center">หมายเหตุ</div></th>
      </tr>
    </thead>
    <tbody>
    <?php $confirm = array( 'onclick' => "return confirm('ต้องการลบข้อมูลหรือไม่?')");?>
      <?php $i = 1 ?>
	  <?php foreach($product as $product){ ?>
      <tr>
        <td><div align="center"><?php echo $i ?></div></td>
        <td><?php echo $product['product_code']?></td>
        <td align="center"><?php echo $product['category_name']?></td>
        <td><?php echo $product['product_name']?></td>
        <td><div align="center">

          <?php
          $this->db->where('stock_shop',@$_SESSION['employees_shop']);
          $this->db->where('stock_type','in');
          $this->db->where('stock_product',$product['product_code']);
          $in = $this->db->get('stock');
          $in_stock_amount = $in->result_array();

          $amount_in=0; foreach ($in_stock_amount as $in_amount) {
            $amount_in += $in_amount['stock_amount'];
          }

          $this->db->where('stock_shop',@$_SESSION['employees_shop']);
          $this->db->where('sale_order_detail_status',1);
          $this->db->join('stock','stock.sale_order_detail_id = sale_order_detail.sale_order_detail_id');
          $this->db->where('stock_type','out');
          $this->db->join('product','product.product_code = stock.stock_product');
          $out = $this->db->get('sale_order_detail');
          $out_stock_amount = $out->result_array();

          $amount_out=0; foreach ($out_stock_amount as $out_amount) {
            $amount_out += $out_amount['stock_amount'];
          }

        echo number_format($stock_amount = ($amount_in - $amount_out));
        ?>
            </div></td>
            <td align="center"><?php echo $product['product_unit']?></td>
            <td><div align="center">
            <?php
      	 if(($product['product_limit_max'])<$stock_amount){
				echo "<span style='color:green;'>คงเหลือปกติ</span>";
			}else{
				echo "<span style='color:red;'>คงเหลือน้อยกว่าเกณฑ์</span>";
			}
		?>
        </div></td>
        <td align="center"><?php echo $product['product_note']?></td>
      </tr>
      <?php $i++ ?>
	  <?php } ?>
    </tbody>
  </table>
</div>
