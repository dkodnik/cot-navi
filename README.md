# Navigation Builder

Builds category structure menus in Cotonti templates

## List menu

Use this widget to build a flat list:

```php
function navi_list($tpl = 'navi.list', $parent = '', $current = '', $count = 1, $sort = 'path', $blacklist = '', $whitelist = '')
```

### Parameters

 * `$tpl` (string) - TPL file name without extension, e.g. 'navi.list' => 'navi.list.tpl'
 * `$parent` (string) - Parent category code for the list. Empty to start at structure root. Accepts special keywords:
   * `__parent__` => parent category of $current,
   * `__root__` => select root-level categories
 * `$current` (string) - Category code that is marked in the list as current, optional
 * `$count` (integer) - Count items. `1` = yes (default), `0` = no (optimized speed)
 * `$sort` (string) - Sort by: `path` (default) or `title` (optional)
 * `$blacklist` (string) - Category codes black list, separated by `;` (optional)
 * `$whitelist` (string) - Category codes white list, separated by `;` (optional)

### TPL usage example

```html
{PAGE_CAT|navi_list('my_menu', '__parent__', $this, 1)}
```

### Template customization

Copy the `navi/tpl/navi.list.tpl` to your theme and customize it to fit your site. Pass customized template name (without extension) as the first argument of the widget function.

## Tree menu

Use `navi_tree` function to generate a nested menu containing categories, subcategories, etc.

```php
function navi_tree($tpl = 'navi.tree', $root = '', $current = '', $depth = 1, $full = 0, $show_root = 0, $siblings = 0, $count = 1, $blacklist = '', $whitelist = '')
```

### Parameters

 * `$tpl` (string) - TPL file name without extension, e.g. 'navi.tree' => 'navi.tree.tpl'
 * `$root` (string) - Root category code for the tree. Empty to start at structure root. Accepts special keywords:
   * `__parent__` => parent category of $current,
   * `__root__` => select root-level categories
 * `$current` (string) - Category code that is marked in the list as current, optional
 * `$depth` (integer) - Max. depth from root, `0` = unlimited. Default is `1`.
 * `$full` (integer) - Show full tree. `1` = open all tree nodes, `0` = show only current branch (default)
 * `$show_root` (integer) - Show root element, `1` = yes, `0` = no (default)
 * `$siblings` (integer) - Show root's siblings, `1` = yes, `0` = no (default)
 * `$count` (integer) - Count items. `1` = yes (default), `0` = no (optimized speed)
 * `$sort` (string) - Sort by: `path` (default) or `title` (optional)
 * `$blacklist` (string) - Category codes black list, separated by `;` (optional)
 * `$whitelist` (string) - Category codes white list, separated by `;` (optional)

### TPL usage example

```html
{PAGE_CAT|navi_list('tree_menu', '__root__', $this, 3, 1, 1, 1)}
```

### Template customization

Copy the `navi/tpl/navi.tree.tpl` to your theme and customize it to fit your site. Pass customized template name (without extension) as the first argument of the widget function.

Note that default template only supports up to 3 levels of nesting. You can add more levels but you should code it in the TPL file similarly to the existing levels.

## Tabular menu

You can create 2-dimensional table of site categories with this function:

```php
function navi_table($tpl = 'navi.table', $parent = '', $current = '', $count = 1, $sort = 'path', $blacklist = '', $whitelist = '')
```

### Parameters

 * `$tpl` (string) - TPL file name without extension, e.g. 'navi.table' => 'navi.table.tpl'
 * `$parent` (string) - Parent category code for the list. Empty to start at structure root. Accepts special keywords:
   * `__parent__` => parent category of $current,
   * `__root__` => select root-level categories
 * `$current` (string) - Category code that is marked in the list as current, optional
 * `$count` (integer) - Count items. `1` = yes (default), `0` = no (optimized speed)
 * `$sort` (string) - Sort by: `path` (default) or `title` (optional)
 * `$blacklist` (string) - Category codes black list, separated by `;` (optional)
 * `$whitelist` (string) - Category codes white list, separated by `;` (optional)

### TPL usage example

```html
{PAGE_CAT|navi_table('my_menu', '__parent__', $this, 1)}
```

### Template customization

Copy the `navi/tpl/navi.table.tpl` to your theme and customize it to fit your site. Pass customized template name (without extension) as the first argument of the widget function.

