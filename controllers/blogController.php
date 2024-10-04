<?php defined('BILLINGMASTER') or die; 


class blogController extends baseController {

    /**
     * ГЛАВНАЯ СТРАНИЦА БЛОГА
     */
    public function actionIndex() {

        $blog = System::CheckExtensension('blog', 1);
        if (!$blog) {
            ErrorPage::return404();
        }
        
        $canonical = $this->settings['script_url'].'/blog';
        $params = unserialize(System::getExtensionSetting('blog'));
        $now = time();
        
        /*  Pagination  */
        $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
        
        $show_items = $params['params']['postcount'];
		$sort = isset($params['params']['sort']) ? $params['params']['sort'] : 'post_id';
		
        $total_post = Blog::countAllPost(0, 1);

        $is_pagination = false;
        if ($total_post > $show_items) {
            $is_pagination = true;
            $pagination = new Pagination($total_post, $page, $show_items);
        }
        
        $post_list = Blog::getPostPublicList($now, 0, $page, $show_items, $sort);

        $this->setSEOParams($params['params']['title'], $params['params']['desc'],
            $params['params']['keys'], $params['params']['h1']
        );

        $this->setViewParams('blog',
            'blog/index.php', [
                ['title' => System::Lang('BLOG')]
            ], $params['params'], 'blog-page'
        );

        require_once ("{$this->template_path}/main.php");
        return true;
    }
    
    
    
    // СТРАНИЦА КАТЕГОРИИ БЛОГА
    public function actionRubric($alias)
    {
        $params = unserialize(System::getExtensionSetting('blog'));
        $blog = System::CheckExtensension('blog', 1);
        if (!$blog) {
            ErrorPage::return404();
        }

		$canonical = $this->settings['script_url'].'/blog/'.$alias;
		$user_groups = $user_planes = false;
        $sort = isset($params['params']['sort']) ? $params['params']['sort'] : 'post_id';
		
        $alias = htmlentities($alias);
        $rubric = Blog::getRubricByAlias($alias);
		$user_id = User::isAuth();
        
        if ($rubric) {
            $access = Blog::CheckAccess($rubric, $user_id);

            /*  Pagination  */
            $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
            $show_items = $params['params']['postcount'];
            $total_post = Blog::countAllPost($rubric['id']);
            $is_pagination = false;

            if ($total_post > $show_items) {
                $is_pagination = true;
                $pagination = new Pagination($total_post, $page, $show_items);
            }
            
            $now = time();
            $post_list = Blog::getPostPublicList($now, $rubric['id'], $page, $show_items, $sort);

            $this->setSEOParams($rubric['title'], $rubric['meta_desc'], $rubric['meta_keys']);
            $this->setViewParams('blog');

            if ($access) {
                $this->setViewParams('blog', 'blog/category.php', [
                        [
                            'title' => System::Lang('BLOG'),
                            'url' => '/blog',
                        ],
                        ['title' => $rubric['name']],
                    ], $params['params'], 'blog-page');
            } else {
                $this->setViewParams('blog', 'blog/no_access.php', [
                    [
                        'title' => System::Lang('BLOG'),
                        'url' => '/blog',
                    ],
                    ['title' => 'Нет доступа'],
                ], null, 'blog-page-no-access');
            }

            require_once ("{$this->template_path}/main.php");
        } else {
            require_once ("{$this->template_path}/404.php");
        }
        return true;
    }


    /**
     * СТРАНИЦА ЗАПИСИ
     * @param $rubric
     * @param $alias
     */
    public function actionPost($rubric, $alias)
    {
        $blog = System::CheckExtensension('blog', 1);
        if (!$blog) {
            ErrorPage::return404();
        }

        $canonical = $this->settings['script_url'].'/blog/'.$rubric.'/'.$alias;
        $params = unserialize(System::getExtensionSetting('blog'));
        $comments = $params['params']['comments'] == 1 ? 1 : false;

        // Сегментация
        $user_id = User::isAuth();
        $no_count = array(10,3);
        if ($user_id && !in_array($user_id, $no_count)) {
            $url = htmlentities($_SERVER["REQUEST_URI"]);
            $url = explode("?", $url);
            $url = $url[0];
            $segment = Blog::Segmentation($user_id, $url);
        }
        
        $rubric = htmlentities($rubric);
        $alias = htmlentities($alias);
        $rubric_data = Blog::getRubricByAlias($rubric);
        $access = false;

        if (!$rubric_data) {
            ErrorPage::return404();
        } else {
			if ($rubric_data['access_type'] > 0) {
                if ($user_id) {
                    if ($rubric_data['access_type'] == 1) {
                        $user_groups = User::getGroupByUser($user_id);
                        
                        $groups_arr = json_decode($rubric_data['groups'], true);
                        if ($user_groups) {
                            foreach($user_groups as $group) {
                                if (in_array($group, $groups_arr)) $access = true;
                            }
                        }
                    }
                    
                    if ($rubric_data['access_type'] == 2) {
                        $membership = System::CheckExtensension('membership', 1);
                        if ($membership) {
                            $user_planes = Member::getPlanesByUser($user_id);
                            $planes_arr = json_decode($rubric_data['planes'], true);

                            if ($user_planes) {
                                foreach($user_planes as $plane) {
                                    if (in_array($plane, $planes_arr)) $access = true;
                                }
                            }
                        }      
                    }
                }
            } else {
			    $access = true;
            }
		}
        
        $post = Blog::getPostByRubric($rubric_data['id'], $alias);
        if ($post) {
            $this->setSEOParams($post['title'], $post['meta_desc'], $post['meta_keys']);
            $this->setViewParams('blog');

            $hit = Blog::writeHit($post['post_id'], $post['hits'] + 1);
            
            $og_image = !empty($post['post_img']) ? '/images/post/cover/'.$post['post_img'] : false;
            
            if ($access) {
                $this->setViewParams('blog', 'blog/post.php', [
                        [
                            'title' => System::Lang('BLOG'),
                            'url' => '/blog',
                        ],
                        [
                            'title' => $rubric_data['name'],
                            'url' => "/blog/{$rubric_data['alias']}",
                        ],
                        ['title' => $post['name']]
                    ], $params['params'], 'blog-page'
                );
            } else {
                $this->setViewParams('blog', 'blog/no_access.php', [
                    [
                        'title' => System::Lang('BLOG'),
                        'url' => '/blog',
                    ],
                    ['title' => 'Нет доступа'],
                ], null, 'blog-page-no-access');
            }

            require_once ("{$this->template_path}/main.php");
        } else {
            require_once ("{$this->template_path}/404.php");
        }
        return true;
    }
}