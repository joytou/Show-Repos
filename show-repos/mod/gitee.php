<?php
class SHOW_REPOS_MOD_GITEE{
	public $api_url 		= 'https://gitee.com/api/v5/repos/{:user}/{:repo}';
	public $template_css 	= 'css/gitee.css';
	public $template_js 	= 'js/gitee.js';
	public $template_html 	= 'gitee.html';
	public function data_format($original_data){
		$data = array();
		$data['name'] 			= $original_data['name'];
		$data['description'] 	= $original_data['description'];
		$data['url'] 			= 'https://gitee.com/'.$original_data['full_name'];
		$data['download_url'] 	= 'http://github.com/'.$original_data['full_name'].'/repository/archive/master';
		$data['owner'] 			= $original_data['owner']['login'];
		$data['owner_url'] 		= $original_data['owner']['html_url'];
		$data['subscribers'] 	= $original_data['stargazers_count'];
		$data['watchers'] 		= $original_data['watchers_count'];
		$data['forks'] 			= $original_data['forks_count'];
		$data['home_page']		= $original_data['homepage'];
		$data['branch']			= $original_data['default_branch'];
		$data['ctime']			= $original_data['created_at'];
		$data['mtime']			= $original_data['updated_at'];
		$data['ptime']			= $original_data['pushed_at'];
		return $data;
	}
}