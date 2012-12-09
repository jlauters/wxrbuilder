<?php 

/* wxr_builder
 * a lightweight developer oriented class
 * to facilitate WXR generation during
 * migrations to WordPress
 * @author Jon Lauters
 */

class wxr_builder {

    public function __construct() { }

    public static function factory() {
        return new wxr_builder();
    }

    public static function maybe_encode($str) {

        if(!mb_detect_encoding($str, "UTF-8", true)) {
            return utf8_encode($str);
        }

        return $str;
    }

    public function get_category_line($data) {

        $term_slug = self::maybe_encode($data['slug']);
        $taxonomy  = self::maybe_encode($data['tax']);
        $term_name = self::maybe_encode($data['label']);

        $category_line = <<<EOHTML
<category domain="{$taxonomy}" nicename="{$term_slug}"><![CDATA[{$term_name}]]></category>
EOHTML;

        return $category_line;
    }

    public function tag_line($file_handle, $data) {

        $slug = self::maybe_encode(self::make_slug($data['tag']));
        $tag  = self::maybe_encode($data['tag']);

        $tag_line = <<<EOHTML
<wp:tag>
  <wp:tag_slug>{$slug}</wp:tag_slug>
  <wp:tag_name><![CDATA[{$tag}]]></wp:tag_name>
</wp:tag>
EOHTML;

        fwrite($file_handle, $tag_line);
    }

    public function author_line($file_handle, $data) {

        $author = self::maybe_encode($data['author']);
        $first  = self::maybe_encode($data['first_name']);
        $last   = self::maybe_encode($data['last_name']);

        $author_line = <<<EOHTML
<wp:author>
  <wp:author_id></wp:author_id>
  <wp:author_login></wp:author_login>
  <wp:author_email></wp:author_email>
  <wp:author_display_name><![CDATA[{$author}]]></wp:author_display_name>
  <wp:author_first_name><![CDATA[{$first}]]></wp:author_first_name>
  <wp:author_last_name><![CDATA[{$last}]]></wp:author_last_name>
</wp:author>
EOHTML;

        fwrite($file_handle, $author_line);
    }

    public static function prepare_content($content) {
        $content = preg_replace('/[\r\n]+/', ' ', $content);
        $content = str_replace('[pagebreak]', '<!--nextpage-->', $content); //drupal to wp conversion

        return $content;
    }

    public function item_line($file_handle, $post, $post_meta, $categories) {

        $id          = self::maybe_encode(isset($post['ID']) ? $post['ID'] : '');
        $title       = self::maybe_encode($post['title']);
        $slug        = self::maybe_encode(self::make_slug($post['title']));
        $link        = self::maybe_encode(isset($post['link']) ? $post['link'] : '');
        $description = self::maybe_encode($post['description']);
        $excerpt     = self::maybe_encode($post['excerpt']);
        $content     = self::maybe_encode(self::prepare_content($post['content']));
        $post_date   = self::maybe_encode($post['create_date']);
        $post_type   = $post['post_type'];
        
        $post_meta_chunk = '';
        foreach($post_meta as $meta) {
            $post_meta_chunk .= $meta."\n";
        }

        $categories_chunk = '';
        foreach($categories as $cat) {
            $categories_chunk .= $cat."\n";
        }

        $item_line = <<<EOHTML
\n<item>
  <title><![CDATA[{$title}]]></title>
  {$categories_chunk}
  <link>{$link}</link>
  <pubDate>{$post_date}</pubDate>
  <dc:creator><![CDATA[]]></dc:creator>

  <guid isPermaLink="false">{$link}</guid>
  <description><![CDATA[{$description}]]></description>
  <content:encoded><![CDATA[{$content}]]></content:encoded>
  <excerpt:encoded><![CDATA[{$excerpt}]]></excerpt:encoded>
  <wp:post_id>{$id}</wp:post_id>
  <wp:post_date>{$post_date}</wp:post_date>
  <wp:post_date_gmt>{$post_date}</wp:post_date_gmt>
  <wp:comment_status>open</wp:comment_status>
  <wp:ping_status>open</wp:ping_status>
  <wp:post_name>{$slug}</wp:post_name>
  <wp:status>publish</wp:status>
  <wp:post_parent>0</wp:post_parent>
  <wp:menu_order>0</wp:menu_order>
  <wp:post_type>{$post_type}</wp:post_type> 
  <wp:post_password></wp:post_password>
  <wp:is_sticky>0</wp:is_sticky>

  {$post_meta_chunk}
    
</item>
EOHTML;

        fwrite($file_handle, $item_line);
    }

    public static function attachment_item_line($file_handle, $attachment) {
    
        $id         = self::maybe_encode($attachment['id']);
        $basename   = self::maybe_encode($attachment['basename']);
        $url        = self::maybe_encode($attachment['url']);
        $date       = self::maybe_encode($attachment['pub_date']);
        $slug       = self::maybe_encode($attachment['slug']);
        $parent_id  = self::maybe_encode($attachment['parent_id']);

        $attachment_line = <<<EOHTML
\n<item>
    <title><![CDATA[{$basename}]]></title>
    <link></link>
    <description><![CDATA[]]></description>
    <guid isPermaLink="false">{$url}</guid>
    <pubDate>{$date}</pubDate>
    <dc:creator></dc:creator>
    <wp:post_id>{$id}</wp:post_id>
    <wp:post_date>{$date}</wp:post_date>
    <wp:post_date_gmt>{$date}</wp:post_date_gmt>
    <wp:comment_status>open</wp:comment_status>
    <wp:ping_status>open</wp:ping_status>
    <wp:post_name>{$slug}</wp:post_name>
    <wp:status>inherit</wp:status>
    <wp:post_parent>{$parent_id}</wp:post_parent>
    <wp:menu_order>0</wp:menu_order>
    <wp:post_type>attachment</wp:post_type>
    <wp:post_password></wp:post_password>
    <wp:attachment_url>{$url}</wp:attachment_url>
</item>
EOHTML;

        fwrite($file_handle, $attachment_line);
    }

    public static function get_post_meta_line($meta_key, $meta_value) {

        $key   = self::maybe_encode($meta_key);
        $value = self::maybe_encode($meta_value);

        $meta_line = <<<EOHTML
<wp:post_meta>
  <wp:meta_key>{$key}</wp:meta_key>
  <wp:meta_value><![CDATA[{$value}]]></wp:meta_value>
</wp:post_meta>
EOHTML;

        return $meta_line;
    }

    public function term_line($file_handle, $data) {

        $slug     = self::maybe_encode(self::make_slug($data['term']));
        $taxonomy = self::maybe_encode($data['taxonomy']);
        $term     = self::maybe_encode($data['term']);
    
        $term_line = <<<EOHTML
<wp:term>
  <wp:term_taxonomy>{$taxonomy}</wp:term_taxonomy>
  <wp:term_slug>{$slug}</wp:term_slug>
  <wp:term_parent></wp:term_parent>
  <wp:term_name><![CDATA[{$term}]]></wp:term_name>
</wp:term>
EOHTML;

        fwrite($file_handle, $term_line);
    }

    public function make_slug($str) {

        $slug = strtolower(trim($str));
        $slug = str_replace(array("&", ".", "'", "/"), "", $slug);
        $slug = preg_replace('/[^A-Za-z0-9]/', ' ', $slug);
        $slug = str_replace('  ', ' ', $slug);
        $slug = str_replace(' ', '-', $slug);

        return $slug;
    }

    public function write_xml_open($file_handle) {

        $xml_open = <<<EOHTML
<?xml version="1.0" encoding="UTF-8"?>

<rss version="2.0"
  xmlns:excerpt="http://wordpress.org/export/1.0/excerpt/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:wp="http://wordpress.org/export/1.0/"
>

<channel>
  <title></title>
  <link></link>
  <description></description>
  <pubDate></pubDate>
  <generator></generator>
  <language>en</language>
  <wp:wxr_version>1.1</wp:wxr_version>
  <wp:base_site_url></wp:base_site_url>
  <wp:base_blog_url></wp:base_blog_url>
EOHTML;

        fwrite($file_handle, $xml_open);
    }

    public function write_xml_close($file_handle) {

        $xml_close = <<<EOHTML
\n</channel>
</rss>
EOHTML;

        fwrite($file_handle, $xml_close);
    }
}
