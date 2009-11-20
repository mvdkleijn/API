<?php
/**
 * @package wolf
 * @subpackage plugin.api
 *
 * @author IAN DUNDAS with band-x <contact@iandundas.co.uk>
 * @version 0.0.1
*
 * $id
 * $user Array (
    [user_id] => 4
    [id] => 1
    [key] => password
    [throttle_limit] => 0
    [enabled] => 0
)

 */
extract($user);
?>

<h1><?php echo __('Edit User'); ?></h1>

<div id="editUser">
	<form method="post" action="/admin/plugin/api/userauth/<?php echo $id?>">
		 <label for="user_id">Global User ID:</label>
		 <select name="user_id[]" id="user_id" size="10">
			 <?php if (sizeof($allusers)>0)
					foreach($allusers as $alluser):?>
						<option value="<?php echo $alluser['id']?>" <?php echo (isset($user_id)&&$alluser['id']==$user_id)?'selected':''?>><?php echo $alluser['username']?></option>
			 <?php	endforeach;?>
		 </select>

		 <label for="key">Key:</label>
		 <input type="text" name="key" id="key" value="<?php echo isset($key)?$key:''?>" />

		 <label for="throttle_limit">Throttle Limit:</label>
		 <input type="text" name="throttle_limit" id="throttle_limit" value="<?php echo isset($throttle_limit)?$throttle_limit:''?>" />

		 <label for="enabled">Enabled:</label>
		 <input type="checkbox" name="enabled" id="enabled" <?php echo (isset($enabled)&&$enabled==1)?'checked':''?> />

		 <label for="submit">Submit:</label>
		 <input type="submit" name="submit" id="submit" />

		 <input type="hidden" name="id" value="<?php echo $id?>" />
	 </form>
</div>