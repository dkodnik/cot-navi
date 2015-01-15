<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

/**
 * Compares 2 category entries by title. Used to sort categories by alphabet
 *
 * @param array $cat1 Category 1
 * @param array $cat2 Category 2
 * @return boolean
 */
function navi_catcmp($cat1, $cat2)
{
	global $structure;
	$t1 = $structure['page'][$cat1]['title'];
	$t2 = $structure['page'][$cat2]['title'];
	if ($t1 == $t2)
	{
		return true;
	}
	else
	{
		return $t1 > $t2 ? 1 : -1;
	}
}

/**
 * Generates category tags array
 *
 * @param string  $code       Category code
 * @param string  $tag_prefix Tag prefix
 * @param boolean $count      Count items
 * @return array
 */
function navi_generate_cattags($code, $tag_prefix = '', $count = true)
{
	global $cot_extrafields, $db, $db_structure, $structure;
	if ($count)
	{
		$sub_count = $db->query("SELECT SUM(structure_count) FROM $db_structure
			WHERE structure_path LIKE '".$db->prep($structure['page'][$code]['rpath']).".%'
			OR structure_path = ".$db->quote($structure['page'][$code]['rpath']))->fetchColumn();
	}
	$sub_url_path = array('c' => $code);
	$temp_array = array(
		$tag_prefix . 'ID'  => $structure['page'][$code]['id'],
		$tag_prefix . 'CAT'   => $code,
		$tag_prefix . 'URL'   => cot_url('page', $sub_url_path),
		$tag_prefix . 'TITLE' => htmlspecialchars($structure['page'][$code]['title']),
		$tag_prefix . 'DESC'  => $structure['page'][$code]['desc'],
		$tag_prefix . 'ICON'  => $structure['page'][$code]['icon'],
		$tag_prefix . 'COUNT' => $count ? $sub_count : ''
	);

	// Extra fields for structure
	foreach ($cot_extrafields[$db_structure] as $row_c)
	{
		$uname = strtoupper($row_c['field_name']);
		$temp_array[$tag_prefix.$uname.'_TITLE'] = isset($L['structure_'.$row_c['field_name'].'_title']) ?  $L['structure_'.$row_c['field_name'].'_title'] : $row_c['field_description'];
		$temp_array[$tag_prefix.$uname] = cot_build_extrafields_data('structure', $row_c, $structure['page'][$code][$row_c['field_name']]);
	}

	return $temp_array;
}

/**
 * Gets parent category code
 *
 * @param string $current Current category
 * @return string
 */
function navi_get_parent($current)
{
	$path = cot_structure_parents('page', $current);
	return(count($path) > 1) ? $path[count($path) - 2] : '__root__';
}

/**
 * Returns root-level categories
 *
 * @return array
 */
function navi_get_roots()
{
	global $structure;
	$roots = array();
	foreach ($structure['page'] as $code => $cat)
	{
		if (mb_strpos($cat['path'], '.') === false)
		{
			$roots[] = $code;
		}
	}
	return $roots;
}

/**
 * A TPL callback that generates a navigation list
 *
 * @param string  $tpl      TPL file name without extension, e.g. 'navi.list' => 'navi.list.tpl'
 * @param string            $parent Parent category code for the list. Empty to start at structure root.
 * Keywords:
 *		'__parent__' => parent category of $current,
 *		'__root__' => select root-level categories
 * @param string  $current   Category code that is marked in the list as current, optional
 * @param integer $count     Count items. 1 = yes, 0 = no
 * @param string  $sort      Sort by: 'path' or 'title'
 * @param string  $blacklist Category codes black list, separated by ';'
 * @param string  $whitelist Category codes white list, separated by ';'
 * @return string
 */
function navi_list($tpl = 'navi.list', $parent = '', $current = '', $count = 1, $sort = 'path', $blacklist = '', $whitelist = '')
{

	// Support for keywords
	if ($parent == '__parent__')
	{
		// Parrent of $current
		$parent = navi_get_parent($current);
	}

	// Get the cats
	$cats = ($parent == '__root__') ? navi_get_roots() : cot_structure_children('page', $parent, false, false, true, false);

	if (!empty($blacklist))
	{
		$bl = explode(';', $blacklist);
		$cats = array_diff($cats, $bl);
	}

	if (!empty($whitelist))
	{
		$wl = explode(';', $whitelist);
		$cats = array_intersect($cats, $wl);
	}

	if (!$cats || count($cats) == 0)
	{
		return '';
	}

	if ($sort == 'title')
	{
		usort($cats, 'navi_catcmp');
	}

	// Display them as list
	$t = new XTemplate(cot_tplfile($tpl, 'plug'));

	$num = 1;
	foreach ($cats as $cat)
	{
		$t->assign(navi_generate_cattags($cat, 'NAVI_LIST_ITEM_', (bool) $count));
		$t->assign(array(
			'NAVI_LIST_ITEM_CURRENT' => ($cat == $current) ? 'current' : '',
			'NAVI_LIST_ITEM_NUM' => $num,
			'NAVI_LIST_ITEM_ODDEVEN' => cot_build_oddeven($num)
		));
		$t->parse('MAIN.NAVI_LIST_ITEM');
		$num++;
	}

	$t->parse();
	return $t->text();
}

/**
 * A TPL callback that generates category navigation as 2D table
 *
 * @param string  $tpl       TPL file name without extension, e.g. 'navi.table' => 'navi.table.tpl'
 * @param string  $parent    Parent category code for the table. Empty to start at structure root
 * @param string  $current   Category code that is marked in the table as current, optional
 * @param integer $count     Count items. 1 = yes, 0 = no
 * @param string  $sort      Sort by: 'path' or 'title'
 * @param string  $blacklist Category codes black list, separated by ';'
 * @param string  $whitelist Category codes white list, separated by ';'
 * @return string
 */
function navi_table($tpl = 'navi.table', $parent = '', $current = '', $count = 1, $sort = 'path', $blacklist = '', $whitelist = '')
{
	// Support for keywords
	if ($parent == '__parent__')
	{
		// Parrent of $current
		$parent = navi_get_parent($current);
	}

	// Get column cats
	$cats = ($parent == '__root__') ? navi_get_roots() : cot_structure_children('page', $parent, false, false, true, false);
	$cols = count($cats);

	if (!empty($blacklist))
	{
		$bl = explode(';', $blacklist);
		$cats = array_diff($cats, $bl);
	}

	if (!empty($whitelist))
	{
		$wl = explode(';', $whitelist);
		$cats = array_intersect($cats, $wl);
	}

	if ($cols == 0)
	{
		return '';
	}

	// Get children for each column
	$children = array();
	$rows = 0;
	foreach ($cats as $cat)
	{
		$tmp = cot_structure_children('page', $cat, true, false, true, false);
		if ($sort == 'title')
		{
			usort($tmp, 'navi_catcmp');
		}
		$children[$cat] = $tmp;
		if (count($tmp) > $rows)
		{
			$rows = count($tmp);
		}
	}

	// Render the table
	$t = new XTemplate(cot_tplfile($tpl, 'plug'));

	foreach ($cats as $cat)
	{
		$t->assign(navi_generate_cattags($cat, 'NAVI_TAB_COL_', (bool) $count));
		$t->assign(array(
			'NAVI_TAB_COL_CURRENT' => ($cat == $current) ? 'current' : ''
		));
		$t->parse('MAIN.NAVI_TAB_COL');
	}

	for ($i = 0; $i < $rows; $i++)
	{
		$t->assign('NAVI_TAB_ROW_NUM', $i);
		for ($j = 0; $j < $cols; $j++)
		{
			$t->assign('NAVI_TAB_COL_NUM', $j);
			$col = $cats[$j];
			if (isset($children[$col][$i]))
			{
				$cat = $children[$col][$i];
				$t->assign(navi_generate_cattags($cat, 'NAVI_TAB_ROW_CELL_', (bool) $count));
				$t->assign(array(
					'NAVI_TAB_ROW_CELL_CURRENT' => ($cat == $current) ? 'current' : '',
					'NAVI_TAB_ROW_CELL_EXISTS' => 1,
				));
			}
			else
			{
				$t->assign('NAVI_TAB_ROW_CELL_EXISTS', 0);
			}
			$t->parse('MAIN.NAVI_TAB_ROW.NAVI_TAB_ROW_CELL');
		}
		$t->parse('MAIN.NAVI_TAB_ROW');
	}

	$t->parse();
	return $t->text();
}

/**
 * A TPL callback that generates tree navigation structure
 *
 * @param string  $tpl       TPL file name without extension, e.g. 'navi.tree' => 'navi.tree.tpl'
 * @param string  $root      Root category code for the tree. Empty to start at structure root
 * @param string  $current   Category code that is marked in the tree as current, optional
 * @param integer $depth     Max. depth from root, 0 = unlimited
 * @param integer $full      Show full tree. 1 = open all tree nodes, 0 = show only current branch
 * @param integer $show_root Show root element, 1 = yes, 0 = no
 * @param integer $siblings  Show root's siblings, 1 = yes, 0 = no
 * @param integer $count     Count items. 1 = yes, 0 = no
 * @param string  $blacklist Category codes black list, separated by ';'
 * @param string  $whitelist Category codes white list, separated by ';'
 * @return string
 */
function navi_tree($tpl = 'navi.tree', $root = '', $current = '', $depth = 1, $full = 0, $show_root = 0, $siblings = 0, $count = 1, $blacklist = '', $whitelist = '')
{
	global $structure;
	// Support for keywords
	if ($root == '__parent__')
	{
		// Parrent of $current
		$root = navi_get_parent($current);
	}

	// Build the tree of categories to display
	$roots = array();

	// Get root-level cats
	if ($root == '__root__')
	{
		// Full tree
		$roots = navi_get_roots();
	}
	else
	{
		if ($siblings)
		{
			$roots = cot_structure_children('page', navi_get_parent($root), false, false, true, false);
		}
		else
		{
			$roots = $show_root ? array($root) : cot_structure_children('page', $root, false, false, true, false);
		}
	}

	// Get main path
	$mainpath = $structure['page'][$current]['path'];
	$mpatharr = explode('.', $mainpath);

	// Black/white lists
	$bl = empty($blacklist) ? false : explode(';', $blacklist);
	$wl = empty($whitelist) ? false : explode(';', $whitelist);

	$t = new XTemplate(cot_tplfile($tpl, 'plug'));

	$num = 1;
	foreach ($roots as $cat)
	{
		if (($full || in_array($cat, $mpatharr))
				&& (!$bl || !in_array($cat, $bl))
				&& (!$wl || in_array($cat, $wl)))
		{
			navi_tree_walk($t, $cat, $current, 1, $depth, $mainpath, $full, $count, $bl, $wl, $num);
			$num++;
		}
	}

	$t->parse();
	return $t->text();
}

/**
 * Recursively parses a category in a tree.
 * Do not call this function from templates!
 *
 * @param XTemplate $t Template object
 * @param string  $cat       Category to process
 * @param string  $current   Category code that is marked in the tree as current, optional
 * @param integer $level     Current tree nesting level
 * @param integer $depth     Max. depth from root, 0 = unlimited
 * @param string  $mainpath  Current category path, used if $full = 0
 * @param integer $full      Show full tree. 1 = open all tree nodes, 0 = show only current branch
 * @param integer $count     Count items. 1 = yes, 0 = no
 * @param array   $blacklist Category codes black list
 * @param array   $whitelist Category codes white list
 * @param integer $num       Number in the current flat list
 */
function navi_tree_walk($t, $cat, $current, $level, $depth, $mainpath, $full, $count, $blacklist, $whitelist, $num)
{
	global $structure;

	$mpatharr = explode('.', $mainpath);

	// Parse children items
	$child_cnt = 0;
	if (!$depth || $level < $depth)
	{
		$children = cot_structure_children('page', $cat, false, false, true, false);
		foreach ($children as $subcat)
		{
			if (($full || in_array($subcat, $mpatharr)
						|| mb_strpos($structure['page'][$subcat]['path'], $mainpath) === 0)
					&& (!$bl || !in_array($subcat, $bl))
					&& (!$wl || in_array($subcat, $wl)))
			{
				navi_tree_walk($t, $subcat, $current, $level + 1, $depth, $mainpath, $full, $count, $blacklist, $whitelist, $child_cnt + 1);
				$child_cnt++;
			}
		}
	}

	// Parse self
	$t->assign(navi_generate_cattags($cat, "NAVI_TREE_LEVEL_{$level}_", (bool) $count));
	$t->assign(array(
		"NAVI_TREE_LEVEL_{$level}_CURRENT"  => ($cat == $current) ? 'current' : '',
		"NAVI_TREE_LEVEL_{$level}_CHILDREN" => $child_cnt,
		"NAVI_TREE_LEVEL_{$level}_NUM"      => $num
	));
	$block_name = 'MAIN';
	for ($i = 1; $i <= $level; $i++)
	{
		$block_name .= ".NAVI_TREE_LEVEL_$i";
	}
	$t->parse($block_name);
}
