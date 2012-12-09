<h1>wxrbuilder</h1>
------------------
lightweight developer oriented class to help facilitate
quick flexible generation of the WXR format for migrating
content into WordPress.

<h3>example usage</h3>

<pre>
require_once 'wxrbuilder/wxrbuilder.php';

// set up your db connection (to old data)
$db_conn = get_db();

// open our wxr file for writing
$wxr_file = fopen('new_content.wxr', 'w');

// write opening of wxr
wxr_builder::factory()->write_xml_open($wxr_file);

/* do the necessary data manipulation to build up arrays for
 * items
 * categories
 * postmeta
 * users
 * authors
 */

// write closing of wxr
wxr_builder::factory()->write_xml_close($wxr_file);

// close our file
fclose($wxr_file);

// close any db connections
close_db($db_conn);
</pre>
