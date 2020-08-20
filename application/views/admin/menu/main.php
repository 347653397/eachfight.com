<?php 
$this->load->view('admin/template/header');
?>
<title>首页</title>
</head>
<body>
<nav class="breadcrumb"><i class="Hui-iconfont">&#xe67f;</i> 首页 <a class="btn btn-success radius r" style="line-height:1.6em;margin-top:3px" href="javascript:location.replace(location.href);" title="刷新" ><i class="Hui-iconfont">&#xe68f;</i></a></nav>
<div class="page-container">
	<p class="f-20 text-success">欢迎登录猪游纪管理后台</p>
	<span><iframe name="weather_inc" src="http://i.tianqi.com/index.php?c=code&id=1" width="330" height="35" frameborder="0" marginwidth="0" marginheight="0" scrolling="no"></iframe></span>
	<table class="table table-border table-bordered table-bg mt-20">
		<thead>
			<tr>
				<th colspan="2" scope="col">服务器信息</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<th width="30%">服务器计算机名</th>
				<td><span id="lbServerName"><?php echo $info['SCRIPT_FILENAME'];?></span></td>
			</tr>
			<tr>
				<td>服务器IP地址</td>
				<td><?php echo $info['REMOTE_ADDR'];?></td>
			</tr>
			<tr>
				<td>服务器域名</td>
				<td><?php echo $info['SERVER_NAME'];?></td>
			</tr>
			<tr>
				<td>服务器端口 </td>
				<td><?php echo $info['REMOTE_ADDR'];?></td>
			</tr>
			<tr>
				<td>服务器操作系统 </td>
				<td><?php echo $info['REMOTE_ADDR'];?></td>
			</tr>
			<tr>
				<td>服务器当前时间 </td>
				<td><?php echo $info['REMOTE_ADDR'];?></td>
			</tr>
			<tr>
				<td>当前系统用户名 </td>
				<td><?php echo $info['REMOTE_ADDR'];?></td>
			</tr>
		</tbody>
	</table>
</div>
</body>
</html>