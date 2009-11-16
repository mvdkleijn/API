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

<h1><?php echo __('API Method Usage'); ?></h1>
<div id="apiUsage">
<table cellspacing="0" summary="List of users filtered and sorted according to the criteria (if any) you have chosen.">
    <thead>
        <tr>
            <th class="tc2" scope="col">Method Name</th>
            <th class="tc2" scope="col">Total Hits</th>
            <th class="tc2" scope="col">Last Accessed</th>
        </tr>
    </thead>
    <tbody>
        <?php $odd = true; foreach($usageMetrics as $usage) { ?>
		<?php if($usage['api_result']>0)$result_class='success';
			  elseif($usage['api_result']==0)$result_class='neutral';
			  else $result_class='error';?>

        <tr class="<?php echo $odd?'odd':'even'; ?>">
            <td class="tc2 <?php echo $result_class?>"><?php echo $usage['method_name']; ?></td>
            <td class="tc2 <?php echo $result_class?>"><?php echo $usage['total_hits']; ?></td>
			<td class="tc1 <?php echo $result_class?>"><?php echo $usage['prettytime'] ?> ago</td>

        </tr>
        <?php $odd?$odd=false:$odd=true; } ?>
    </tbody>
</table>
</div>