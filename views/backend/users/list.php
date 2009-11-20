<?php
/**
 * @package wolf
 * @subpackage plugin.api
 *
 * @author IAN DUNDAS with band-x <contact@iandundas.co.uk>
 * @version 0.0.1
 */
?>

<h1><?php echo __('User Authentication'); ?></h1>
<div id="api">
<p><a href="/admin/plugin/api/userauth/add">Add user</a></p>
<table cellspacing="0" summary="List of users filtered and sorted according to the criteria (if any) you have chosen.">
    <thead>
        <tr>
            <th class="tc0" scope="col">Username</th>
            <th class="tc1" scope="col">ID</th>
            <th class="tc2" scope="col">Key</th>
            <th class="tc2" scope="col">Total Hits</th>
            <th class="tc2" scope="col">Throttle Limit</th>
            <th class="tc2" scope="col">Last Accessed</th>
        </tr>
    </thead>
    <tbody>
        <?php $odd = true; foreach($users as $user) { ?>

		    <!--[id] => 1
            [name] => Administrator
            [email] => contact@iandundas.co.uk
            [username] => admin
            [password] => md5 hash
            [language] => en
            [created_on] =>
            [updated_on] =>
            [created_by_id] =>
            [updated_by_id] =>
            [user_id] => 4
            [key] => password-->
		<?php
			if ($user['enabled']==1):
				$class='enabled';
			  else:
				$class='disabled';
			endif;
		?>
		<?php $editlink = '/admin/plugin/api/userauth/'.$user['id'];?>
        <tr class="<?php echo $odd?'odd':'even'; ?>"  onclick="javascript: document.location.href='<?php echo $editlink;?>';">
            <td class="tc0 <?php echo $class?>"><a href="#"><?php echo $user['username']; ?></a></td>
            <td class="tc1 <?php echo $class?>"><?php echo $user['id']; ?></td>
            <td class="tc2 <?php echo $class?>"><?php echo $user['key']; ?></td>
            <td class="tc2 <?php echo $class?>"><?php echo $user['total_hits']; ?></td>
            <td class="tc2 <?php echo $class?>"><?php echo $user['throttle_limit']; ?></td>
            <td class="tc2 <?php echo $class?>"><?php echo (isset($user['prettytime']))?$user['prettytime'].' ago':' never'; ?>
			</td>
        </tr>
        <?php $odd?$odd=false:$odd=true; } ?>
    </tbody>
</table>
</div>