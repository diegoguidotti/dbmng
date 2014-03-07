<!DOCTYPE html> 
<html>
<head>

	<title>DBMNG</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="stylesheet" href="../libs/jquery.mobile-1.3.2.min.css" />
	<script src="../libs/jquery-1.9.1.min.js"></script>
	<script src="../libs/jquery.mobile-1.3.2.min.js"></script>
	<script src="../libs/jstorage.min.js"></script>

	<script type="text/javascript" src="../library/dbmng/assets/dbmng_obj.js?mpvqml"></script>
	<script type="text/javascript" src="../library/dbmng/assets/dbmng_widgets.js?mpvqml"></script>
	<script type="text/javascript" src="../library/dbmng/assets/dbmng_mobile.js?mpvqml"></script>
	<!-- <link rel="stylesheet" href="../library/dbmng/assets/dbmng_mobile.css" /> -->


	<style type="text/css" media="all">
		@import url("../library/dbmng/assets/dbmng.css");
	</style>
</head>
<body>



	<div data-role="page"  id="login_page">
		<div data-role="header" >
			<h1>DBMNG - Login</h1>
			<a href="#table_list" data-icon="home" data-role="button">Home</a>
		</div>
		<div data-role="content" >
			<div id="login_message" ></div>
			<form    action=""  onsubmit="return doLogin()" id="login_form" method="post" data-ajax="false" >
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for= "user_id">userID  </label>
					<input type="text" name="user_id"   id="user_id"   value="" placeholder="userID">
				</div>		
				<div data-role="fieldcontain" class="ui-hide-label">
					<label for="password">password</label>
					<input type="password" name="password" id="password" value="" placeholder="password">
				</div>
				<button type="submit" data-theme="b" name="submit" value="submit-value">userID</button>
				<span id="dialog_login"></span>
			</form>
			
			<form  style="display:none;" action=""  onsubmit="return doLogout()" id="logout_form" method="post" data-ajax="false" >
				<button type="submit" data-theme="b" name="submit" value="submit-value">Logout</button>
				<span id="dialog_login"></span>
			</form>
		</div>
	</div>


	<div data-role="page"  id="table_list">
		<div data-role="header" >
			<h1>DBMNG - Table List</h1>
			<a href="#table_list" data-icon="home" data-role="button">Home</a>
			<a onClick="goToLogin()" data-icon="gear" data-role="button">Tools</a>
		</div>
		<div data-role="content" > 
			<!-- <div id="table_list_container"></div> -->
		</div>
		<div data-role="footer" data-position="fixed">
	    <div data-role="controlgroup" data-type="horizontal">
	      <a data-role=button onClick="dbReset()" id=db_reset> Reset </a> 
	    </div>
	  </div>
	</div>


	<div data-role="page"  id="table_edit">
		<div data-role="header" data-position="fixed">
			<h1><span id="table_edit_header">Edit Table<span></h1>
			<a href="#table_list" data-icon="home" data-role="button">Home</a>
			<a onClick="goToLogin()" data-icon="gear" data-role="button">Tools</a>
		</div>
		<div data-role="content" > 
			<div id="table_edit_container"></div>
		</div>

	  <div data-role="footer" data-position="fixed">
	    <div data-role="controlgroup" data-type="horizontal">
	      <a data-role=button id=record_add> Add </a> 
	      <a data-role=button id=record_save> Save </a> 
	      <a data-role=button id=record_reset> Reset </a> 
	    </div>
	  </div>
	</div>

	<div data-role="page"  id="record_edit">
		<div data-role="header" id="header">
			<a href="#table_edit" data-icon="arrow-l" data-role="button">Back</a>

			<div data-type="horizontal" data-role="controlgroup" class="ui-btn-right">  
		    <a id="record_edit_dup" data-role="button" data-inline="true" data-icon="myapp-copy">Duplicate</a> 
		    <a id="record_edit_del" data-role="button" data-inline="true" data-icon="delete" data-iconpos="right">Delete</a>	<!-- data-icon="delete" -->
		  </div>

<!--
      <ul id="menu-right" data-role="menu">
          <li>
              <span data-role="button" data-icon="arrow-d" data-iconpos="right">Actions</span>
              <ul data-role="listview" data-inset="true">
                 <li data-icon="false"><div id="record_edit_dup"></div></li>
                 <li data-icon="false"><div id="record_edit_del"></div></li>
              </ul>
          </li>
      </ul>
-->        
			<h1>Edit Record</h1>
			
		</div>

		<div data-role="content" >
			<!-- <div id="record_edit_container"></div> -->
		</div>

	</div>



	<script type="text/javascript">

		var base_call="";
		//var base_call="http://www.michelemammini.it/dbmng/tests/";//ajax_mobile.php";

		jQuery(document).ready(function() {
		//jQuery(document).on('mobileinit', function() {
			init_mobile();
		});
	</script>
</body>
</html>
