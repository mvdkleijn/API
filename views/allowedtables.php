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
	<!--
     [18] => Array
        (
            [Tables_in_avfest_ian] => av_programme_collectives
            [id] => 2
            [tablename_slug] => collectives
            [tablename] => programme_collectives
            [columns] => id;name;description
            [clean_name] => programme_collectives
            [raw_name] => av_programme_collectives
        )

 or

     [17] => Array
        (
            [Tables_in_avfest_ian] => av_programme_cities
            [raw_name] => av_programme_cities
        )

 -->
<table cellspacing="0" summary="List of users filtered and sorted according to the criteria (if any) you have chosen.">
    <thead>
        <tr>
            <th class="tc1" scope="col">Table</th>
            <th class="tc2" scope="col">Slug</th>
            <th class="tc2" scope="col">Columns</th>
            <th class="tc2" scope="col">[Edit]</th>
        </tr>
    </thead>
    <tbody>
        <?php $odd = true;print_r($tables);exit; foreach($tables as $table) { ?>
			<?php $is_enabled=(array_key_exists('enabled', $table) && $table['enabled']==1);?>
			<tr class="<?php echo $odd?'odd':'even'; ?>">
				<td class="tc2 <?php echo $is_enabled?'enabled':'disabled'; ?>">
					<a href="#">
						<dfn title="<?php echo $table['raw_name']; ?>"><?php echo $table['clean_name']; ?></dfn>
					</a>
				</td>
				<td class="tc2 <?php echo $is_enabled?'enabled':'disabled'; ?>"><?php echo (array_key_exists('tablename_slug', $table))?$table['tablename_slug']:''; ?></td>
				<td class="tc2 <?php echo $is_enabled?'enabled':'disabled'; ?>"><a href='#'><?php echo (array_key_exists('columns', $table))?$table['columns']:'none:[add]'; ?></a></td>
				<td class="tc2 <?php echo $is_enabled?'enabled':'disabled'; ?>">
					<?php if ($is_enabled):?>
						<a href='#'>[edit]</a>
					<?php else: ?>
						<a href='/admin/plugin/api/allowedentities/add?tablename_slug=<?echo $table['clean_name']?>&tablename=<?echo $table['raw_name']?>'>[add]</a>
					<?php endif;?>
				</td>

			</tr>
        <?php $odd?$odd=false:$odd=true; } ?>
    </tbody>
</table>
</div>