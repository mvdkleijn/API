<?php
/**
 * @package wolf
 * @subpackage plugin.api
 *
 * @author IAN DUNDAS with band-x <contact@iandundas.co.uk>
 * @version 0.0.1
*
 * $id
 * $table Array (
 *	[id] => 2
 *	[tablename_slug] => collectives
 *	[tablename] => programme_collectives
 *	[columns] => id;name;description
 *	[enabled] => 1
 * )
 */
extract($table);
$allcolumns = $table['allcolumns'];
$currentcolumns = array_flip(explode (';', $columns));
?>

<h1><?php echo __('API Method Usage'); ?></h1>

<div id="apiUsage">
	<form method="post">
		 <label for="tablename_slug">Slug:</label>
		 <input type="text" name="tablename_slug" id="tablename_slug" value="<?php echo isset($tablename_slug)?$tablename_slug:''?>" />
		 
		 <label for="tablename">Tablename:</label>
		 <input type="text" name="tablename" id="tablename" value="<?php echo isset($tablename)?$tablename:''?>" />
		 
		 <label for="columns">API-accessible Columns:</label>
		 <select name="columns[]" id="columns" multiple size="10">
			 <?php if (sizeof($allcolumns)>0)
					foreach($allcolumns as $column):?>
						<option name="<?php echo $column['column_name']?>" value="<?php echo $column['column_name']?>" <?php echo (array_key_exists($column['column_name'], $currentcolumns))?'selected':''?>><?php echo $column['column_name']?></option>
			 <?php	endforeach;?>
		 </select>

		 <label for="enabled">Enabled:</label>
		 <input type="checkbox" name="enabled" id="enabled" <?php echo ($enabled==1)?'checked':''?> />

		 <label for="submit">Submit:</label>
		 <input type="submit" name="submit" id="submit" />

		 <input type="hidden" name="id" value="<?php echo $id?>" />
	 </form>
</div>