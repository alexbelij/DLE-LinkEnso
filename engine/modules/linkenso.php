<?php

/*
=============================================================================
 ����: linkenso.php (frontend) ������ 2.3
-----------------------------------------------------------------------------
 �����: ����� ��������� ����������, mail@mithrandir.ru
-----------------------------------------------------------------------------
 ������: ��������, pafnuty10@gmail.com, http://pafnuty.name
-----------------------------------------------------------------------------
 ���� ���������: http://alaev.info/blog/post/3982
-----------------------------------------------------------------------------
 ����������: ��������� ������������ �������� �� �����
=============================================================================
*/

/**
 * ChangeLog:
 * v.2.2.3 � 07.04.2014
 * - ������������� � ����� �������� ����� �������� � ������ DLE 10.2 � ����.
 *
 * 
 * v.2.2.2 � 18.03.2014
 * - ���������� ������ � �������� ������������ ��������� ������ (���� �������� ���� metatitle � �������).
 *
 * 
 * v.2.2.1 � 06.03.2014
 * - ���������� ������ � DLE 10.0 (�������� � � 10.1), ����� "���������� ������" � ������ ������� ���������� ALT ��������. ����� ����� ����������� ������� span ��� ����������� �������� ��� ��������� ����������� ��������. �� ���� ����� ��� ������������ ������� ����� php 
 *
 * 
 * v.2.2.0 � 03.02.2014
 * - ���������� ������ "�����������" ��������� ����������� �������.
 * - ��������� 4 ����� ����:
 * 		{link-category} - ������� ����� �� ��� ���������, ����� �������, � ������� ����������� �������.
 *		{category} - ������� �������� ���������, � ������� ����������� �������.
 *		{category-icon} - ������� ��� ������ ���������, � ������� ��������� ������� (���� ������� ����������� � 5�� ���������� - ����� �������� 5 ������). � ����� linkenso �������� ������� ����� ���������� �������� ������� � ������ noicon.png
 *		{category-url} - ������� ������ URL �� ���������, ������� ����������� ������ �������.
 *
 * 
 * v.2.1.1 � 27.11.2013
 * - ���������� ������ � ������������� ��� � ������� DLE <9.6
 * - ������ ����������� � ������� (�������� ��������� � ������ ������)
 *
 * 
 * v.2.1 � 02.11.2013
 * - ������ ����� �� DLE_API - ������ ������ �������� ������� ������� � ���������� ������� ������ ��������. �� � ������ ��������, ��� � �������� �������� ����� ��� ���� �.�. ��������� ���� ������� �� "���-���".
 * - ��������� ����� (�� ���������) ��� �������� ������ ��� �������� (�� ����� �������� �� ������ ������� dle).
 * - ����������� ������������ ������ ������� ��� ������ ������.
 * - ����� �� �������-������, ������ ��, ��� ������ ���� ������� ������� �������� (����� �������) ������ ����������� ������ ������ �����������.
 * - ��������� ����� ����������� ������ �������, ������ ��� {full-story} ������� ������ �������, � �� ��������.
 * - ���� ��� ������ �������� ��� �������� ������� ��� ����� - ����� ����� ���������.
 * - �������� ��� {link-url} - ������� ������ URL �� �������.
 * - �������� ���� [not_show_image] - ������� �����, ���� �������� � ����� ���.
 */

// ���������
if (!defined('DATALIFEENGINE')) {
	die("Hacking attempt!");
}

/*
 * ����� ��� ������ ������ �����
 */
if (!class_exists('LinkEnso')) {
	class LinkEnso {
		/*
		 * ����������� ������ LinkEnso 
		 * @param $linkEnsoConfig - ������ � ������������� ������
		 */
		public function __construct($linkEnsoConfig) {
			global $db, $config, $category, $tpl, $cat_info;
			$this->dle_config = $config;
			$this->db = $db;
			$this->tpl = $tpl;
			$this->cat_info = $cat_info;

			// ������ ������������ ������
			$this->config = $linkEnsoConfig;
		}


		/*
		 * ������� ����� ������ LinkEnso
		 */
		public function run() {
			// ������� ���������� ���������� ������ �� ����
			$output = false;
			if ($this->dle_config['allow_cache'] && $this->dle_config['allow_cache'] != "no") {
				$output = dle_cache('linkenso_', md5(implode('_', $this->config)) . $this->dle_config['skin']);
			}

			// ���� �������� ���� ��� ������ ������������ ��������, ������� ���������� ����
			if ($output !== false) {
				$this->showOutput($output);
				return;
			}

			// ���� � ���� ������ �� �������, ���������� ������ ������
			$wheres = array();

			// �������� ���������� � ���, � ����� ���������� ����� ������ ����
			$post = $this->db->super_query("SELECT category FROM " . PREFIX . "_post WHERE id = '" . $this->config['postId'] . "'");

			// ���������� ����� ����� DLE API - ����� ����� ������ ���������� ������ ������ � ������, ���� ������ �� �������
			if (!empty($post) && !empty($post['category'])) $postCategories = array();

			$postCategories = explode(',', $post['category']);

			// �������� ������ ��������� ��� ������� � ����������� �� ��������� scan
			$categoriesArray = array();
			switch ($this->config['scan']) {
				// ���� ����� ����������� ������ ������� ���������
				case 'same_cat':
					// ������ ��������� �������� ����� � ��� �� ������������ ��������� � ����� ������
					foreach ($postCategories as $postCategory) {
						$postCategory = intval($postCategory);
						$categoriesArray[] = $postCategory;
						$categoriesArray = array_merge($categoriesArray, $this->getSubcategoriesArray($postCategory));
					}
					break;

				// ���� ����� ����������� ��� ������������ ����� "������� ���������"
				case 'global_cat':
					// ��� ������ �� ��������� �������� ����� ������� �������� ��������� � ��� � ������������
					foreach ($postCategories as $postCategory) {
						$postCategory = intval($postCategory);
						$globalCategoryId = $this->getGlobalCategory($postCategory);
						$categoriesArray[] = $globalCategoryId;
						$categoriesArray = array_merge($categoriesArray, $this->getSubcategoriesArray($globalCategoryId));
					}
					break;

				default:
					break;
			}

			// ������� �� ������ ���������
			if (count($categoriesArray) > 0) {
				switch ($this->dle_config['allow_multi_category']) {
					// ���� �������� ��������� ���������������
					case '1':
						$categoryWheres = array();
						foreach ($categoriesArray as $categoryId) {
							$categoryWheres[] = 'category regexp "[[:<:]](' . str_replace(',', '|', $categoryId) . ')[[:>:]]"';
						}
						$wheres[] = '(' . implode(' OR ', $categoryWheres) . ')';
						break;

					// ���� ��������� ��������������� ���
					default:
						$wheres[] = 'category IN (' . implode(',', $categoriesArray) . ')';
						break;
				}
			}

			// � ����������� �� ��������� date ���������� ������ ��� ����� ����� ��� �����
			switch ($this->config['date']) {
				case 'new':
					$dateWhere = 'id > ' . $this->config['postId'];
					break;

				default:
					$dateWhere = 'id < ' . $this->config['postId'];
					break;
			}

			// ������� ��� ����������� ������ ������, ��������� ���������
			$wheres[] = 'approve = 1';

			// ������� ��� ����������� ������ ��� ������, ���� ���������� ������� ��� ���������
			$wheres[] = 'date < "' . date("Y-m-d H:i:s") . '"';

			// ������� ��� ���������� �������� id
			$wheres[] = 'id != ' . $this->config['postId'];

			// ���������� �������
			$where = implode(' AND ', $wheres);

			// ����������� ���������� ������� �� ����, ������ �� ������� ��� ������ (��� ������ - ASC, ��� ������ - DESC)
			$ordering = $this->config['date'] == 'new' ? 'ASC' : 'DESC';

			// ���� �� ��, ������� ��������� �������� (������������ � ���� �����x, ������� ���������� � ���� ���������� �� ������ ���� ���� ����� ��������� ��� ��)
			$fields = 'id, date, short_story, full_story, xfields, title, metatitle, category, alt_name, approve';
			$fields .= ($this->dle_config['version_id'] < 9.6) ? ', flag' : '' ; // ��� ������ dle �������� ���� flag

			// ������ ���� - ��������� ���������� ������
			$posts = $this->db->super_query("SELECT " . $fields . " FROM " . PREFIX . "_post WHERE " . $where . " AND " . $dateWhere . " ORDER BY id " . $ordering . " LIMIT 0, " . $this->config['links'], true);

			// ���������� ����� ����� DLE API - ����� ����� ������ ���������� ������ ������ � ������, ���� ������ �� �������
			if (empty($posts)) $posts = array();

			// ������ ���� - ���� � ������ ����������� ������ �� ������� � �������� ring ���������� ��� 1, ���� ����� � ������ �������
			if (count($posts) < $this->config['links'] && $this->config['ring'] == 'yes') {
				// ������ ������ id ������, ����� ������������� ��
				$posts_id_array = array();
				foreach ($posts as $post) {
					$posts_id_array[] = $post['id'];
				}

				// ������� ��� ���������� ��� ���������� ��������
				if (!empty($posts_id_array)) {
					$wheres[] = 'id NOT IN(' . implode(',', $posts_id_array) . ')';
				}

				// ���������� �������
				$where = implode(' AND ', $wheres);

				// �������� ���. ����� �� �����
				$morePosts = $this->db->super_query("SELECT " . $fields . " FROM " . PREFIX . "_post WHERE " . $where . " ORDER BY id " . $ordering . " LIMIT 0, " . ($this->config['links'] - count($posts)), true);

				// ���������� ����� ����� DLE API - ����� ����� ������ ���������� ������ ������ � ������, ���� ������ �� �������
				if (empty($morePosts)) $morePosts = array();

				$posts = array_merge_recursive($posts, $morePosts);
			}

			// ��������� ������ ������
			$linksOutput = '';
			foreach ($posts as $post) {
				// ��������� ������ �� ��������� � ������ ���������
				$my_cat = array();
				$my_cat_icon = array();
				$my_cat_link = array();
				$cat_list = explode(',', $post['category']);
				foreach($cat_list as $element) {
					if(isset($this->cat_info[$element])) {
						$my_cat[] = $this->cat_info[$element]['name'];
						if ($this->cat_info[$element]['icon'])
							$my_cat_icon[] = '<img class="category-icon" src="'.$this->cat_info[$element]['icon'].'" alt="'.$this->cat_info[$element]['name'].'" />';
						else
							$my_cat_icon[] = '<img class="category-icon" src="{THEME}/linkenso/noicon.png" alt="'.$this->cat_info[$element]['name'].'" />';
						if($this->dle_config['allow_alt_url'] == 'yes') 
							$my_cat_link[] = '<a href="'.$this->dle_config['http_home_url'].get_url($element).'/">'.$this->cat_info[$element]['name'].'</a>';
						else 
							$my_cat_link[] = '<a href="'.$PHP_SELF.'?do=cat&category='.$this->cat_info[$element]['alt_name'].'">'.$this->cat_info[$element]['name'].'</a>';
					}
				}
				$categoryUrl = ($post['category']) ? $this->dle_config['http_home_url'] . get_url(intval($post['category'])) . '/' : '/' ;


				// ������� �����
				$post['short_story'] = stripslashes($post['short_story']);
				$post['full_story'] = stripslashes($post['full_story']);

				// ����� �����������
				$image = '';
				switch ($this->config['image']) {
					// ������ ����������� �� �������� ��������
					case 'short_story':
						$image = $this->getContentImage($post['short_story'], 0);
						break;

					// ������ ����������� �� ������� ��������
					case 'full_story':
						$image = $this->getContentImage($post['full_story'], 0);
						break;

					// �� ��������� - �������� ��������������� ����
					default:
						$xfields = xfieldsdataload($post['xfields']);
						if (!empty($xfields) && !empty($xfields[$this->config['image']])) {
							$image = $xfields[$this->config['image']];
						}
						break;
				}

				$linksOutput .= $this->applyTemplate($this->config['template'], array(
					'{link}'         => '<a ' . ($this->config['title'] != 'empty' ? 'title="' . ($this->config['title'] == 'name' ? stripslashes($post['title']) : stripslashes($post['metatitle'])) . '"' : '') . ' href="' . ($this->getPostUrl($post)) . '">' . ($this->config['anchor'] == 'title' ? stripslashes($post['metatitle']) : stripslashes($post['title'])) . '</a>',
					'{anchor}'       => $this->config['anchor'] == 'title' ? stripslashes($post['metatitle']) : stripslashes($post['title']),
					'{title}'        => $this->config['title'] != 'empty' ? ($this->config['title'] == 'name' ? stripslashes($post['title']) : stripslashes($post['metatitle'])) : '',
					'{short-story}'  => $this->crobContent($post['short_story'], $this->config['limit']),
					'{full-story}'   => $this->crobContent($post['full_story'], $this->config['limit']),
					'{image}'        => $image,
					'{link-url}'     => $this->getPostUrl($post),
					'{link-category}'=> implode(', ', $my_cat_link),
					'{category}'	 => implode(', ', $my_cat),
					'{category-icon}'=> implode('', $my_cat_icon),
					'{category-url}' => $categoryUrl,
				), array(
					"'\[link\\](.*?)\[/link\]'si"                     => '<a ' . ($this->config['title'] != 'empty' ? 'title="' . ($this->config['title'] == 'name' ? stripslashes($post['title']) : stripslashes($post['metatitle'])) . '"' : '') . ' href="' . ($this->getPostUrl($post)) . '">' . "\\1" . '</a>',
					"'\[show_image\\](.*?)\[/show_image\]'si"         => !empty($image) ? "\\1" : '',
					"'\[not_show_image\\](.*?)\[/not_show_image\]'si" => empty($image) ? "\\1" : '',
				));
			}
			// 
			$output = $linksOutput;

			// ���� ��������� �����������, ��������� � ��� �� ������ ������������
			if ($this->dle_config['allow_cache'] && $this->dle_config['allow_cache'] != "no") {
				create_cache('linkenso_', $output, md5(implode('_', $this->config)) . $this->dle_config['skin']);
			}

			// ������� ���������� ������
			$this->showOutput($output);
		}


		/*
		 * ����� ���������� ���������� ������ ���� ������������ ������������ ���������
		 * @param $categoryId - ������������� �������� ���������
		 * @return array - ������ �� ������� ������������
		 */
		public function getSubcategoriesArray($categoryId) {
			// �������� $categoryId
			$categoryId = intval($categoryId);

			// ������ �� ������� ������������
			$subcategoriesArray = array();

			// �������� ������ ������������
			$subcategories = $this->db->super_query("SELECT id FROM " . PREFIX . "_category WHERE parentid = " . $categoryId, true);

			if (empty($subcategories)) $subcategories = array();

			foreach ($subcategories as $subcategory) {
				// ��������� � ������ ������� ������������
				$subcategoriesArray[] = intval($subcategory['id']);

				// ��������� � ������ ��� �� ������������
				$subcategoriesArray = array_merge($subcategoriesArray, $this->getSubcategoriesArray($subcategory['id']));
			}

			// ���������� ������ ������������
			return $subcategoriesArray;
		}


		/*
		 * ����� ���������� ������ ���� ������������ ������������ ���������
		 * @param $categoryId - ������������� �������� ���������
		 * @return int - ������������� ����� "�������" ��������� ���� �������
		 */
		public function getGlobalCategory($categoryId) {
			// �������� $categoryId
			$categoryId = intval($categoryId);

			// ������������ ���������� ������ � ����������� � ����������
			// ������ �� ������� ��������� �����, ����� �������� ����� �������� ���������
			while ($this->cat_info[$categoryId]['parentid'] > 0) {
				$categoryId = intval($this->cat_info[$categoryId]['parentid']);
			}

			// ���������� ����� �������� ���������
			return $categoryId;
		}


		/*
		 * @param $post - ������ � ����������� � ������
		 * @return string URL ��� ���������
		 */
		public function getPostUrl($post) {
			
			if ($this->dle_config['allow_alt_url'] && $this->dle_config['allow_alt_url'] != "no") {
				if (
					($this->dle_config['version_id'] < 9.6 && $post['flag'] && $this->dle_config['seo_type'])
					||
					($this->dle_config['version_id'] >= 9.6 && ($this->dle_config['seo_type'] == 1 || $this->dle_config['seo_type'] == 2))
				) {
					if (intval($post['category']) && $this->dle_config['seo_type'] == 2) {
						$url = $this->dle_config['http_home_url'] . get_url(intval($post['category'])) . '/' . $post['id'] . '-' . $post['alt_name'] . '.html';
					}
					else {
						$url = $this->dle_config['http_home_url'] . $post['id'] . '-' . $post['alt_name'] . '.html';
					}
				}
				else {
					$url = $this->dle_config['http_home_url'] . date("Y/m/d/", strtotime($post['date'])) . $post['alt_name'] . '.html';
				}
			}
			else {
				$url = $this->dle_config['http_home_url'] . 'index.php?newsid=' . $post['id'];
			}

			return $url;
		}


		/*
		 * ����� �������� ������ $content �� ����� $length, ������ ��� html-���� � ���������� �
		 * 
		 * @param $content - �������� ������
		 * @param $length - ���������� �������, �� ������� ����� �������� ������
		 * 
		 * @return string - ��������� �������
		 */
		public function crobContent($content = '', $length = 0) {
			$content = preg_replace( "'\<span class=\"highslide-caption\">(.*?)\</span>'si", "", "$content" );
			$content = str_replace("</p><p>", " ", $content);
			$content = strip_tags($content, "<br />");
			$content = trim(str_replace("<br>", " ", str_replace("<br />", " ", str_replace("\n", " ", str_replace("\r", "", $content)))));

			if ($length && dle_strlen($content, $config['charset']) > $length) {
				$content = dle_substr($content, 0, $length, $this->dle_config['charset']);
				if (($temp_dmax = dle_strrpos($content, ' ', $this->dle_config['charset']))) {
					$content = dle_substr($content, 0, $temp_dmax, $this->dle_config['charset']);
				}
			}

			return $content;
		}


		/*
		 * ����� ���������� $index �� ����� ����������� �� ������ $content
		 * 
		 * @param $content - ������ � ��������� ��� ������ �����������
		 * @param $index - ���������� ����� ������������� ����������� ������� � 0
		 */

		public function getContentImage($content, $imageIndex = 0) {
			preg_match_all('/(img|src)=("|\')[^"\'>]+/i', $content, $media);
			$data = preg_replace('/(img|src)("|\'|="|=\')(.*)/i', "$3", $media[0]);

			foreach ($data as $index => $url) {
				if ($index == $imageIndex) {
					$info = pathinfo($url);
					if (isset($info['extension'])) {
						$info['extension'] = strtolower($info['extension']);
						if (($info['extension'] == 'jpg') || ($info['extension'] == 'jpeg') || ($info['extension'] == 'gif') || ($info['extension'] == 'png')) {
							if (substr_count($url, 'data/emoticons') == 0 && substr_count($url, 'dleimages') == 0) return $url;
						}
					}
					$imageIndex++;
				}
			}

			return false;
		}


		/*
		 * ����� ������������ tpl-������, �������� � �� ���� � ���������� ����������������� ������
		 * @param $template - �������� �������, ������� ����� ���������
		 * @param $vars - ������������� ������ � ������� ��� ������ ���������� � �������
		 * @param $vars - ������������� ������ � ������� ��� ������ ������ � �������
		 *
		 * @return string tpl-������, ����������� ������� �� ������� $data
		 */
		public function applyTemplate($template, $vars = array(), $blocks = array()) {
			// ���������� ���� ������� $template.tpl, ��������� ���

			$this->tpl = new dle_template();
			$this->tpl->dir = TEMPLATE_DIR;

			$this->tpl->load_template($template . '.tpl');

			// ��������� ������ �����������
			$this->tpl->set('', $vars);

			// ��������� ������ �������
			foreach ($blocks as $block => $value) {
				$this->tpl->set_block($block, $value);
			}

			// ����������� ������ (��� �� ��� �� �������� ;))
			$this->tpl->compile($template);

			// ������� ���������
			return $this->tpl->result[$template];
		}


		/*
		 * ����� ������� ���������� ������ � �������
		 * @param $output - ������ ��� ������
		 */
		public function showOutput($output) {
			echo $output;
		}
	}
}
/*---End Of LinkEnso Class---*/


// ������������ ������������ ������
$linkEnsoConfig = array(
	'postId'   => !empty($post_id) ? $post_id : false,
	'links'    => !empty($links) ? $links : 3,
	'date'     => !empty($date) ? $date : 'old',
	'ring'     => !empty($ring) ? $ring : 'yes',
	'scan'     => !empty($scan) ? $scan : 'all_cat',
	'anchor'   => !empty($anchor) ? $anchor : 'name',
	'title'    => !empty($title) ? $title : 'title',
	'limit'    => !empty($limit) ? $limit : 0,
	'image'    => !empty($image) ? $image : 'full_story',
	'template' => !empty($template) ? $template : 'linkenso/linkenso'
);

// ������� ��������� ������ ��� ������������ � ��������� ��� ������� �����
$linkEnso = new LinkEnso($linkEnsoConfig);
$linkEnso->run();

?>