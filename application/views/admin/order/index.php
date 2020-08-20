<?php 
$this->load->view('admin/template/header');
?>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <span class="c-gray en">&gt;</span> 订单管理 <span class="c-gray en">&gt;</span> 订单信息 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<div class="text-c">
		<form action='__SELF__' method='get'>
		信息搜索：
		<input type="text" name="ntitle" id="ntitle" placeholder="输入名称" style="width:250px" class="input-text">
		<button class="btn btn-success" type="submit"><i class="Hui-iconfont">&#xe665;</i> 搜索</button>
		</form>
	</div>
	<div class="cl pd-5 bg-1 bk-gray mt-20"> 
	<span class="l">
	<!-- <a class="btn btn-primary radius" onClick='news_add("添加信息","{:U("News/news_add")}")' href="javascript:;"><i class="Hui-iconfont">&#xe600;</i> 添加信息</a> -->
	</span> 
	<span class="r">共有数据：<strong><?php echo $count; ?></strong> 条</span> 
	</div>
	<div class="mt-20">
		<table class="table table-border table-bordered table-bg table-hover table-sort">
			<thead>
				<tr class="text-c">
					<th>订单状态</th>
					<th>用户昵称</th>
					<th>大神昵称</th>
					<th>游戏类型</th>
					<th>游戏模式</th>
					<th>游戏大区</th>
					<th>游戏段位</th>
					<th>每局价格</th>
					<th>游戏局数</th>
					<th>订单金额</th>
					<th>下单时间</th>
					<th>抢单时间</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody>
				<?php  
	            if($list){
	                foreach( $list as $key=>$row):
	            ?>
				<tr class="text-c">
					<td>
						<?php echo order_status()[$row['status']]; ?>
					</td>
					<td><?php echo $row['username']?></td>
					<td><?php echo $row['godusername']?></td>
					<td>
						<?php echo order_status()[$row['game_type']]; ?>
					</td>
					<td>
						<?php echo order_status()[$row['game_mode']]; ?>
					</td>
					<td>
						<?php echo order_status()[$row['game_zone']]; ?>
					</td>
					<td>白银</td>
					<td><?php echo $row['one_price']?></td>
					<td><?php echo $row['game_num']?></td>
					<td><?php echo $row['order_fee']?></td>
					<td><?php echo $row['create_time']?></td>
					<td><?php echo $row['grab_time']?></td>
					<td class="f-14 td-manage">
						<a style="text-decoration:none" class="ml-5" onClick='toview("查看","{:U("News/toview")}?id={$row.id}")' href="javascript:;" title="查看"><i class="Hui-iconfont">&#xe665;</i></a> 
						<a style="text-decoration:none" class="ml-5" onClick='news_edit("修改信息","{:U("News/news_edit")}?id={$row.id}")' href="javascript:;" title="修改信息"><i class="Hui-iconfont">&#xe6df;</i></a> 
						<a style="text-decoration:none" class="ml-5" onClick="DeleteRow({$row['id']})" href="javascript:;" title="删除"><i class="Hui-iconfont">&#xe6e2;</i></a>
					</td>
				</tr>
				<?php 
	                endforeach;
	            }else{
	              echo "<tr><td colspan='8' style='text-align:center'>没有信息！</td></tr>";
	            }
	            ?>
			</tbody>
		</table>
		<div class="page_box_list">
		<!-- <?php echo $page; ?> -->
		</div>
	</div>
</div>
<?php 
$this->load->view('admin/template/foot');
?>
<script type="text/javascript">
//导航添加
function news_add(title,url,w,h){
	layer_show(title,url,w,h);
};
//修改
function news_edit(title,url,w,h){
	layer_show(title,url,w,h);
};
//查看
function toview(title,url,w,h){
	layer_show(title,url,w,h);
};
function DeleteRow(id){
  	layer.confirm('确认要删除吗？',function(index){
		$.post("{:U('News/news_del')}",{id:id},function(res){
            res = eval('(' + res + ')');
            
            if(res.err_code == '200'){
                layer.msg('删除成功', {
                    time: 1000,
                }, function(){
                    location.reload();
                });
            }else{
                layer.msg(res.err_msg);
            }
        });	
	});
}
</script>