<?php
/***********************************************************************

  Copyright (C) 2002-2005  Smartys (smartys@punbb-hosting.com)

  This file is part of PunBB.

  PunBB is free software; you can redistribute it and/or modify it
  under the terms of the GNU General Public License as published
  by the Free Software Foundation; either version 2 of the License,
  or (at your option) any later version.

  PunBB is distributed in the hope that it will be useful, but
  WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston,
  MA  02111-1307  USA

************************************************************************/

define('PUN_QUIET_VISIT', 1);
define('PUN_ROOT', './');
require PUN_ROOT.'include/common.php';

//
// CONFIGURATION BEGINS HERE
//

// false = write to file, true = dynamic
define('GENERATE_DYNAMIC_SITEMAP', true);

// This only matters if you're writing to the file
define('STATIC_SITEMAP_FILENAME', PUN_ROOT . 'sitemap.xml');

//
// CONFIGURATION ENDS HERE
//

if (GENERATE_DYNAMIC_SITEMAP)
	$generator = new DynamicSitemapGenerator();
else
	$generator = new StaticSitemapGenerator(STATIC_SITEMAP_FILENAME);

$generator->addUrl($pun_config['o_base_url'] . '/', time(), null, '1.0');

// Output the data for the forums
$result = $db->query('SELECT f.id as forum_id, last_post, num_topics FROM '.$db->prefix.'forums AS f LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=f.id AND fp.group_id=3) WHERE fp.read_forum IS NULL OR fp.read_forum=1 ORDER BY f.id DESC') or error('Unable to fetch forum list', __FILE__, __LINE__, $db->error());

while ($cur_forum = $db->fetch_assoc($result))
{
	$generator->addUrl($pun_config['o_base_url'] . '/viewforum.php?id=' . $cur_forum['forum_id'], $cur_forum['last_post'], null, '0.5');

	$num_pages = ceil($cur_forum['num_topics'] / $pun_config['o_disp_topics_default']);

	// Add page number for subsequent pages
	for ($i = 2; $i <= $num_pages; ++$i)
	{
		$generator->addUrl($pun_config['o_base_url'] . '/viewforum.php?id=' . $cur_forum['forum_id'] . '&p=' . $i, $cur_forum['last_post'], null, '0.5');
	}
}

// Output the data for the topics
$result = $db->query('SELECT t.id as topic_id, last_post, sticky, num_replies FROM '.$db->prefix.'topics AS t LEFT JOIN '.$db->prefix.'forum_perms AS fp ON (fp.forum_id=t.forum_id AND fp.group_id=3) WHERE (fp.read_forum IS NULL OR fp.read_forum=1) AND t.moved_to IS NULL ORDER BY last_post DESC') or error('Unable to fetch topic list', __FILE__, __LINE__, $db->error());

while ($cur_topic = $db->fetch_assoc($result))
{
	$priority = ($cur_topic['sticky'] == '1') ? '1.0' : '0.75';

	$generator->addUrl($pun_config['o_base_url'] . '/viewtopic.php?id=' . $cur_topic['topic_id'], $cur_topic['last_post'], null, $priority);

	// We add one because the first post is not counted as a reply but needs to be
	// taken into account for display
	$num_pages = ceil(($cur_topic['num_replies'] + 1) / $pun_config['o_disp_posts_default']);

	for ($i = 2; $i <= $num_pages; ++$i)
	{
		$generator->addUrl($pun_config['o_base_url'] . '/viewtopic.php?id=' . $cur_topic['topic_id'] . '&p=' . $i, $cur_topic['last_post'], null, $priority);
	}
}

$generator->completeSitemap();

abstract class SitemapGenerator
{
	protected function beginSitemap()
	{
		global $pun_config;

		$output = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$output .= '<?xml-stylesheet type="text/xsl" href="' . $pun_config['o_base_url'] . '/sitemap.xsl"?>' . "\n";
		$output .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		$this->addToSitemap($output);
	}

	public function completeSitemap()
	{
		$this->addToSitemap('</urlset>' . "\n");
	}

	public function addUrl($loc, $lastmod = null, $changefreq = null, $priority = 0.5)
	{
		$output = "\t" . '<url>' . "\n";
		$output .= "\t\t" . '<loc>' . htmlspecialchars($loc, ENT_QUOTES) . '</loc>' . "\n";

		if ($lastmod != null)
			$output .= "\t\t" . '<lastmod>' . gmdate('Y-m-d\TH:i:s+00:00', $lastmod) . '</lastmod>' . "\n";

		if ($changefreq != null)
			$output .= "\t\t" . '<changefreq>' . $changefreq . '</changefreq>' . "\n";

		$output .= "\t\t" . '<priority>' . $priority . '</priority>' . "\n";

		$output .= "\t" . '</url>' . "\n";

		$this->addToSitemap($output);
	}

	protected abstract function addToSitemap($xml);
}

class StaticSitemapGenerator extends SitemapGenerator
{
	private static $file;

	public function __construct($sitemap_file)
	{
		$this->file = fopen($sitemap_filename, 'w');
		$this->beginSitemap();
	}

	protected function addToSitemap($xml)
	{
		fwrite($this->file, $xml);
	}

	public function completeSitemap()
	{
		parent::completeSitemap();

		fclose($this->file);

		echo 'Done';
	}
}

class DynamicSitemapGenerator extends SitemapGenerator
{
	public function __construct()
	{
		header('Content-type: application/xml');
		$this->beginSitemap();
	}

	protected function addToSitemap($xml)
	{
		echo $xml;
	}
}
