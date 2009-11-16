<?php
/**
 * @package wolf
 * @subpackage plugin.api
 *
 * @author IAN DUNDAS with band-x <contact@iandundas.co.uk>
 * @version 0.0.1
 *
 * displays api usage scrolling by. Not much use yet.
 */
?>

<h1><?php echo __('API Method Usage Stream'); ?></h1>
<div id="apiUsage">
<table cellspacing="0" summary="List of users filtered and sorted according to the criteria (if any) you have chosen.">
    <thead>
        <tr>
            <th class="tc0" scope="col">API auth id</th>
            <th class="tc1" scope="col">Access time</th>
            <th class="tc2" scope="col">Method</th>
            <th class="tc2" scope="col">Result</th>
            <th class="tc2" scope="col">Message</th>
            <th class="tc2" scope="col">IP Address</th>
        </tr>
    </thead>
    <tbody>
        <?php $odd = true; foreach($usageList as $usage) { ?>
		<?php if($usage['api_result']>0)$result_class='success';
			  elseif($usage['api_result']==0)$result_class='neutral';
			  else $result_class='error';?>

        <tr class="<?php echo $odd?'odd':'even'; ?>">
            <td class="tc0 <?php echo $result_class?>">
				<a href="/admin/api/userauth/byauthid/<?php echo $usage['api_auth_id']; ?>">
					<?php echo $usage['api_auth_id']; ?>
				</a>
			</td>
            <td class="tc1 <?php echo $result_class?>">
				<dfn title="<?php echo $usage['accesstime']; ?>">
					<?php echo $usage['prettytime']; ?> ago
				</dfn>
			</td>
            <td class="tc2 <?php echo $result_class?>"><?php echo $usage['api_method']; ?></td>
            <td class="tc2 <?php echo $result_class?>"><?php echo ($usage['api_result']==1)?'Success':'Error'; ?></td>
            <td class="tc2 <?php echo $result_class?>"><?php echo $usage['api_result_message']; ?></td>
            <td class="tc2 <?php echo $result_class?>">
				<a href="http://www.ipaddressreport.com/index.cgi?hostname=<?php echo $usage['ip_address']; ?>">
					<?php echo $usage['ip_address']; ?>
				</a>
			</td>
        </tr>
        <?php $odd?$odd=false:$odd=true; } ?>
    </tbody>
</table>
</div>