<?php
/**
 * @package wolf
 * @subpackage plugin.api
 *
 * @author IAN DUNDAS with band-x <contact@iandundas.co.uk>
 * @version 0.0.1
 
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

<?php //print_r($tables);exit;?>
<script>
var $j = jQuery.noConflict();

 $j(document).ready(function(){
   alert('test')
 });

</script>
<p>Click row to add and edit</p>
<table cellspacing="0" summary="List of users filtered and sorted according to the criteria (if any) you have chosen.">
    <thead>
        <tr>
            <th class="tc1" scope="col">Table</th>
            <th class="tc2" scope="col">Slug</th>
            <th class="tc2" scope="col">Columns</th>
        </tr>
    </thead>
    <tbody>
        <?php $odd = true; foreach($tables as $table) { ?>
			<?php $is_enabled=(array_key_exists('enabled', $table) && $table['enabled']==1);?>
			<?php $is_created=(array_key_exists('id', $table));?>
			<?php
				#TODO: tidy this up with a switch statement
				if ($is_enabled):
					$class='enabled';
				  elseif($is_created):
					$class='notenabled';
				  else:
					$class='disabled';
				endif;

				if ($is_created):
					$editlink = '/admin/plugin/api/allowedentities/'.$table['id'];
				else:
					$editlink = '/admin/plugin/api/allowedentities/add?tablename_slug='.$table['clean_name'].'&tablename='.$table['raw_name'];
				endif;
				?>
			<!-- Would be using jQuery here but it doesn't want to work :'( -->
			<tr class="<?php echo $odd?'odd':'even'; ?>" onclick="javascript: document.location.href='<?php echo $editlink;?>';">
				<td class="tc2 <?php echo $class; ?>">
					<dfn title="<?php echo $table['raw_name']; ?>"><?php echo $table['clean_name']; ?></dfn>
				</td>
				<td class="tc2 <?php echo $class; ?>"><?php echo (array_key_exists('tablename_slug', $table))?$table['tablename_slug']:''; ?></td>
				<td class="tc2 <?php echo $class; ?>"><?php echo (array_key_exists('columns', $table))?$table['columns']:'none:[add]'; ?></td>
			</tr>
        <?php $odd?$odd=false:$odd=true; } ?>
    </tbody>
</table>
</div>