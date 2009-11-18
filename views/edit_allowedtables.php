<?php
/*
 * Forum plugin for Wolf CMS. <http://www.wolfcms.org>
 * Copyright (C) 2009 Martijn van der Kleijn <martijn.niji@gmail.com>
 *
 * This file is part of the Forum plugin for Wolf CMS.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * The Forum plugin provides forum functionality for Wolf CMS.
 *
 * @package wolf
 * @subpackage plugin.forum
 *
 * @author Martijn van der Kleijn <martijn.niji@gmail.com>
 * @version 0.0.1
 * @since Wolf version 0.5.5
 * @license http://www.gnu.org/licenses/gpl.html GPL License
 * @copyright Martijn van der Kleijn, 2009
 */
?>
<h1><?php echo __('Add a forum'); ?></h1>
<div id="forum">
    <div id="new_topic" class="editbox">
      <div class="form">
        <form action="<?php echo BASE_URL; ?>plugin/forum/add" method="post">
        <div>
          <label for="forum[name]"><?php echo __('Name'); ?></label>
          <input class="subject" id="forum[name]" type="text" name="forum[name]" value="<?php if (isset($forum['name'])) echo $forum['name']; ?>" />
        </div>
        <div>
          <label for="forum[description]"><?php echo __('Description'); ?></label>
          <input class="subject" id="forum[description]" type="text" name="forum[description]" value="<?php if (isset($forum['description'])) echo $forum['description']; ?>" />
        </div>
        <p>
          <input type="submit" name="commit" accesskey="s" value="<?php echo __('Submit'); ?>" />
          or <a href="#" nclick="document.getElementById('new_topic').style.display = 'none'; return false;">cancel</a>
        </p>
      </form>
      </div>
      <div class="help">
        <h5>Creating a new forum help</h5>
        <p>
            something, something, bla bla bla...
        </p>
      </div>
    </div>
</div>