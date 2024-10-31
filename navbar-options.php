<div class="wrap" style="max-width:950px !important;">
	<h2>NavBar</h2>
				
	<div id="poststuff" style="margin-top:10px;">
		<div id="sideblock" style="float:right;width:220px;margin-left:10px;"> 
				 <h3>Information</h3>
				 <div id="dbx-content" style="text-decoration:none;">
				 	 <img src="<?php echo $imgpath ?>/home.png"><a style="text-decoration:none;" href="http://www.prelovac.com/vladimir/wordpress-plugins/navbar"> NavBar Home</a><br />
			 <img src="<?php echo $imgpath ?>/rate.png"><a style="text-decoration:none;" href="http://wordpress.org/extend/plugins/navbar/"> Rate this plugin</a><br />
			 <img src="<?php echo $imgpath ?>/help.png"><a style="text-decoration:none;" href="http://www.prelovac.com/vladimir/forum"> Support and Help</a><br />			 
			 <br />
			 <a style="text-decoration:none;" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2567254"><img src="<?php echo $imgpath ?>/paypal.gif"></a>			 
			 <br /><br />
			 <img src="<?php echo $imgpath ?>/more.png"><a style="text-decoration:none;" href="http://www.prelovac.com/vladimir/wordpress-plugins"> Cool WordPress Plugins</a><br />
			 <img src="<?php echo $imgpath ?>/twit.png"><a style="text-decoration:none;" href="http://twitter.com/vprelovac"> Follow updates on Twitter</a><br />			
			 <img src="<?php echo $imgpath ?>/idea.png"><a style="text-decoration:none;" href="http://www.prelovac.com/vladimir/services"> Need a WordPress Expert?</a>
			 					 
		 		</div>
		 	</div>

	 <div id="mainblock" style="width:710px">
	 
		<div class="dbx-content">
		 	<form action="<?php echo $action_url ?>" method="post">
					<input type="hidden" name="submitted" value="1" /> 
					<?php wp_nonce_field('navbar-nonce'); ?>
					<p>Note: To add new shortcuts, simply drag and drop any link from the page to your NavBar.</p>
					
					<select name="position" style="width:117px;">
                        <option value="left"<?php echo ($options['position'] == "left" ? ' selected="selected"':''); ?>>Left</option>
                        <option value="right"<?php echo ($options['position'] == "right" ? ' selected="selected"':''); ?>>Right</option>
                    </select>
                    <label for="position"> NavBar Position</label>
                    <br />					
					<br />
                    
                    <input type="text" name="hotkey" size="15" value="<?php echo $options['hotkey'] ?>"/><label for="hotkey"> Hotkey (Used as Ctrl + <i>hotkey</i>)</label>
                    <br />					
					<br />
					
                    <input type="text" name="width" size="15" value="<?php echo $options['width'] ?>"/><label for="hotkey"> NavBar width in pixels (you can also resize it with your mouse)</label>
                    <br />					
					<br />
					
                    <input type="text" id="navbar-bgcolor" name="bgcolor" size="15" value="<?php echo $options['bgcolor'] ?>"/><label for="bgcolor"> NavBar Background Color</label>
                    <br />
                    <br />
                    <input type="checkbox" name="navinfo"  <?php echo $navinfo ?>/><label for="navinfo"> Show posts and comments</label> <br> 
                    <input type="checkbox" name="navcomment"  <?php echo $navcomment ?>/><label for="navcomment"> Show latest comment</label> <br> 
                    <input type="checkbox" name="navvisitor"  <?php echo $navvisitor ?>/><label for="navvisitor"> Show latest visitor</label> <br> 
                    <input type="checkbox" name="navfilter"  <?php echo $navfilter ?>/><label for="navfilter"> Enable plugin filter (developers can use 'navbar_info' filter to add information to Navbar)</label> <br> 
                    
					<div class="submit"><input type="submit" name="Submit" value="Update" /></div>
			</form>
		</div>
	 </div>

	</div>
	
<h5>WordPress plugin by <a href="http://www.prelovac.com/vladimir/">Vladimir Prelovac</a></h5>
</div>